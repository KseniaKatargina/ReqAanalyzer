import traceback

from app.classifier import classify_sentence

from quart import Blueprint, request, jsonify, current_app
from app.utils import split_text_into_sentences, detect_contradictions, \
    find_ambiguous_terms, can_be_split_by_syntax, AMBIGUOUS_TERMS
from app.celery_app import celery

text_analysis_bp = Blueprint('text_analysis', __name__)


@celery.task
def analyze_text_task(text):
    sentences = split_text_into_sentences(text)

    results = []
    for sentence in sentences:
        recommendations = []

        classification, score = classify_sentence(sentence)

        if classification == "Ambiguous":
            recommendations.append("Предложение является двусмысленным. Уточните формулировку.")

        ambiguous_terms = find_ambiguous_terms(sentence, AMBIGUOUS_TERMS)
        if ambiguous_terms:
            recommendations.append(
                f"Используются неоднозначные термины: {', '.join(ambiguous_terms)}. Желательно уточнить или заменить их.")

        if can_be_split_by_syntax(sentence):
            recommendations.append("Предложение можно разбить на более короткие для улучшения читаемости.")

        results.append({
            "sentence": sentence,
            "classification": classification,
            "score" : score,
            "can_be_split": can_be_split_by_syntax(sentence),
            "ambiguous_terms": ambiguous_terms,
            "recommendations": recommendations
        })

    contradictions = detect_contradictions(sentences)

    for contradiction in contradictions:
        recommendations = [
            "Эти предложения противоречат друг другу."
        ]
        results.append({
            "sentence": f" {contradiction['sentence1']} и {contradiction['sentence2']}",
            "classification": "Contradiction",
            "recommendations": recommendations
        })

    return {"analysis": results, "contradictions": contradictions, "original_text": text}


@text_analysis_bp.route('/analyze', methods=['POST'])
async def analyze_text():
    try:
        raw_data = await request.data
        if not raw_data:
            return jsonify({"error": "Пустое тело запроса"}), 400

        data = await request.get_json()
        if not data:
            return jsonify({"error": "Некорректный JSON"}), 400

        text = data.get("text")
        task = analyze_text_task.delay(text)
        return jsonify({"task_id": task.id}), 202

    except Exception as e:
        current_app.logger.error(f"Ошибка в обработке текста: {str(e)}")
        current_app.logger.error(traceback.format_exc())
        return jsonify({"error": "Внутренняя ошибка сервера"}), 500


@text_analysis_bp.route('/tasks/<task_id>', methods=['GET'])
async def get_task_status(task_id):

    task = analyze_text_task.AsyncResult(task_id)

    if task.state == "PENDING":
        return jsonify({"status": "PENDING"}), 202
    elif task.state == "SUCCESS":
        return jsonify({"status": "SUCCESS", "result": task.result}), 200
    else:
        return jsonify({"status": task.state}), 500