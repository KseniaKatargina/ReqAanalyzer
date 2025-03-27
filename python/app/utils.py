import spacy
import stanza
import re
import torch
from transformers import AutoTokenizer, AutoModelForSequenceClassification

nlp = stanza.Pipeline('ru', processors='tokenize,pos,lemma,depparse')
split = spacy.load("ru_core_news_sm")
AMBIGUOUS_TERMS = [
    "лучший", "самый лучший", "удобный для пользователя", "простой в использовании",
    "рентабельный", "это", "этот", "тот", "почти всегда", "значительный", "минимальный",
    "предоставлять поддержку", "но не ограничиваясь этим", "как минимум", "лучше, чем",
    "более высокое качество", "если возможно", "при необходимости", "без указания ссылки",
    "не должны предоставляться", "дополнительный", "существенный", "расширяемый",
    "достаточный", "адекватный", "актуальный", "пригодный", "типичный", "обычный",
    "текущий", "важный", "любой", "гибкий", "общий", "максимально", "минимально",
    "большое количество", "приблизительно", "сколько-нибудь", "достаточно", "несколько",
    "почти", "общепринятый", "эффективный", "оптимальный", "приемлемый", "эффектный",
    "понятный", "разумный", "в противном случае", "с другой стороны", "после чего",
    "при этом", "некоторые", "кто-либо", "любой", "такой", "все", "оба",
    "в соответствии с общепринятой практикой", "сообразно обстоятельствам",
    "насколько это возможно", "по возможности", "как требуется", "где возможно",
    "допускается", "в рамках целесообразного", "как можно меньше", "как можно больше",
    "включая, но не ограничиваясь", "стандартные форматы", "в типовых условиях",
    "и т. д", "и т. п", "при наличии технологической возможности", "если это осуществимо",
    "по мере надобности", "почти всегда", "допустимо"
]


def split_text_into_sentences(text):
    if not text or not isinstance(text, str):
        return []

    lines = text.splitlines()

    cleaned_lines = []
    for line in lines:
        line = line.strip()
        if not re.match(r'^[А-Яа-я\s]+$|^\d+\.\s[А-Яа-я\s]+$', line) and line:
            cleaned_lines.append(line)

    cleaned_text = " ".join(cleaned_lines)

    sentences = re.split(r'(?<=[.!?])\s+', cleaned_text)

    final_sentences = []
    current_sentence = ""

    for sentence in sentences:
        if re.match(r'^\d+(\.\d+)*\.?\s*', sentence):
            if current_sentence:
                final_sentences.append(current_sentence.strip())
                current_sentence = ""
            current_sentence = sentence
        else:
            current_sentence += " " + sentence.strip()

    if current_sentence:
        final_sentences.append(current_sentence.strip())

    corrected_sentences = []
    for sentence in final_sentences:
        if len(sentence.split()) > 20:
            corrected_sentences.extend(re.split(r'(?<=\w\.)\s+', sentence))
        else:
            corrected_sentences.append(sentence)

    return corrected_sentences

def detect_contradictions(sentences, contradiction_threshold=0.998):
    model = AutoModelForSequenceClassification.from_pretrained("./custom_contradictions_model")
    tokenizer = AutoTokenizer.from_pretrained("./custom_contradictions_model")

    def check_contradiction(sentence_pairs):
        inputs = tokenizer(
            [pair[0] for pair in sentence_pairs],
            [pair[1] for pair in sentence_pairs],
            return_tensors="pt",
            padding=True,
            truncation=True
        )
        with torch.no_grad():
            outputs = model(**inputs)
            probs = torch.nn.functional.softmax(outputs.logits, dim=-1)
        return probs

    sentence_pairs = [
        (sentences[i], sentences[j])
        for i in range(len(sentences))
        for j in range(i + 1, len(sentences))
    ]

    if not sentence_pairs:
        return []

    contradictions = []
    batch_size = 16
    for batch_start in range(0, len(sentence_pairs), batch_size):
        batch = sentence_pairs[batch_start:batch_start + batch_size]
        scores = check_contradiction(batch)
        for pair, score in zip(batch, scores):
            contradiction_score = score[2].item()  # CONFLICT
            neutral_score = score[1].item()  # NEUTRAL
            if contradiction_threshold <= contradiction_score < 0.999 and contradiction_score > neutral_score:
                contradictions.append({
                    "sentence1": pair[0],
                    "sentence2": pair[1],
                    "contradiction_score": contradiction_score
                })

    contradictions = sorted(contradictions, key=lambda x: x['contradiction_score'], reverse=True)
    return contradictions


def find_ambiguous_terms(sentence, terms_list):
    found_terms = set()
    for term in terms_list:
        if re.search(r'\b' + re.escape(term) + r'\b', sentence, re.IGNORECASE):
            found_terms.add(term)
    return list(found_terms)

def can_be_split_by_syntax(sentence, max_dependencies=20, max_depth=8):
    doc = split(sentence)

    sent = next(doc.sents, doc)

    dependency_count = len([token for token in sent if token.dep_ != "punct"])

    def get_depth(token, depth=0):
        if not list(token.children):
            return depth
        return max(get_depth(child, depth + 1) for child in token.children)

    root = [token for token in sent if token.head == token]
    max_actual_depth = max(get_depth(token) for token in root) if root else 0

    return dependency_count > max_dependencies or max_actual_depth > max_depth
