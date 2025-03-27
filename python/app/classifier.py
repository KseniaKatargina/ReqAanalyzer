from transformers import pipeline

from app.utils import AMBIGUOUS_TERMS, find_ambiguous_terms

model_path = "./ambiguity_trained_model"
classifier = pipeline("text-classification", model=model_path, tokenizer=model_path)

def classify_sentence(sentence):
    result = classifier(sentence)
    score = result[0]['score']
    label = result[0]['label']

    classification = 'Unambiguous'

    if label == 'LABEL_1' and score < 0.75:
        classification = 'Ambiguous'
    if label == 'LABEL_0' and score > 0.85:
        classification = 'Unambiguous'
    ambiguous_terms = find_ambiguous_terms(sentence, AMBIGUOUS_TERMS)
    if ambiguous_terms:
        classification = 'Ambiguous'

    return classification, score