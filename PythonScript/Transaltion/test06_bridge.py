import json
import re
import sys
import importlib.util
import contextlib

from pathlib import Path
from functools import lru_cache
from typing import Any, Dict, List, Optional

# -------------------------
# Resolve paths automatically
# -------------------------
BASE_DIR = Path(__file__).resolve().parent
CORE_PATH = BASE_DIR / "test06.py"

if not CORE_PATH.exists():
    raise FileNotFoundError(f"Core script not found: {CORE_PATH}")

# -------------------------
# Load test04.py as module
# Redirect import-time prints to stderr
# -------------------------
spec = importlib.util.spec_from_file_location("subtitle_core", str(CORE_PATH))
if spec is None or spec.loader is None:
    raise RuntimeError(f"Could not load module spec from: {CORE_PATH}")

subtitle_core = importlib.util.module_from_spec(spec)

with contextlib.redirect_stdout(sys.stderr):
    spec.loader.exec_module(subtitle_core)

# -------------------------
# Pull functions / globals from test04.py
# -------------------------
detect_language = subtitle_core.detect_language
resolve_lang_code = subtitle_core.resolve_lang_code
_generate_translation = subtitle_core._generate_translation
split_text_for_meanings = subtitle_core.split_text_for_meanings
_is_punctuation_only = subtitle_core._is_punctuation_only
word_meanings = subtitle_core.word_meanings
sentence_descriptions = subtitle_core.sentence_descriptions
_generate_with_explainer = subtitle_core._generate_with_explainer
LANGUAGE_ALIASES = subtitle_core.LANGUAGE_ALIASES

# Grammar helpers
_get_en_words = subtitle_core._get_en_words
_build_children_map = subtitle_core._build_children_map
_find_root = subtitle_core._find_root
_find_main_verb = subtitle_core._find_main_verb
_find_subject_phrase = subtitle_core._find_subject_phrase
_find_object_phrase = subtitle_core._find_object_phrase
_infer_tense = subtitle_core._infer_tense
_infer_structure = subtitle_core._infer_structure
_default_grammar_notes = subtitle_core._default_grammar_notes
_generate_ai_grammar_notes = subtitle_core._generate_ai_grammar_notes

# Optional optimized helpers from your new test04.py
_get_english_pivot = getattr(subtitle_core, "_get_english_pivot", None)
_sentence_descriptions_from_english = getattr(subtitle_core, "_sentence_descriptions_from_english", None)


# -------------------------
# Internal helpers
# -------------------------
def _quiet_call(func, *args, **kwargs):
    """
    Redirect stdout to stderr for all calls into test04.py so stdout
    remains clean JSON for Laravel / API consumers.
    """
    with contextlib.redirect_stdout(sys.stderr):
        return func(*args, **kwargs)


def _load_payload() -> Dict[str, Any]:
    raw = sys.stdin.read()
    if not raw.strip():
        return {}
    return json.loads(raw)


def _ok(data: Any) -> None:
    print(json.dumps({
        "ok": True,
        "data": data,
    }, ensure_ascii=False))


def _err(message: str) -> None:
    print(json.dumps({
        "ok": False,
        "error": message,
    }, ensure_ascii=False))


@lru_cache(maxsize=8192)
def _clean_token(token: str) -> str:
    cleaned = re.sub(r"^\W+|\W+$", "", token, flags=re.UNICODE)
    return cleaned if cleaned else token


@lru_cache(maxsize=512)
def _translate_small_text(text: str, target_tag: str, max_new_tokens: int = 80) -> str:
    """
    Cache repeated tiny translations (especially repeated notes).
    """
    if not text:
        return text
    if target_tag == "eng_Latn":
        return text
    return _quiet_call(
        _generate_translation,
        text,
        "eng_Latn",
        target_tag,
        max_new_tokens=max_new_tokens,
    )


def _get_english_sentence(sentence: str, source_tag: str, target_tag: str, translated_target: str) -> str:
    """
    Reuse optimized helper from test06.py if available.
    """
    if callable(_get_english_pivot):
        return _quiet_call(
            _get_english_pivot,
            text=sentence,
            source_tag=source_tag,
            target_tag=target_tag,
            translated_text=translated_target,
        )

    if source_tag == "eng_Latn":
        return sentence
    if target_tag == "eng_Latn" and translated_target:
        return translated_target

    return _quiet_call(
        _generate_translation,
        sentence,
        source_tag,
        "eng_Latn",
        max_new_tokens=160,
    )


def _get_sentence_descriptions(sentence: str, source_tag: str, target_tag: str, translated_target: str, english_input: str):
    """
    Reuse optimized helper from test04.py if available, otherwise fall back
    to the public function.
    """
    if callable(_sentence_descriptions_from_english):
        return _quiet_call(
            _sentence_descriptions_from_english,
            english_input,
            source_tag,
            target_tag,
        )

    return _quiet_call(
        sentence_descriptions,
        sentence,
        source_tag,
        target_tag,
        translated_text=translated_target,
    )


# -------------------------
# Grammar object builder
# Output shape remains EXACTLY the same
# -------------------------
def _build_grammar_object(english_text: str) -> Dict[str, Any]:
    words = _quiet_call(_get_en_words, english_text)

    if not words:
        return {
            "tense": "Unknown",
            "structure": "Unknown",
            "subject": "Unknown",
            "verb": "Unknown",
            "object": "Unknown",
            "notes": ["The sentence could not be parsed."],
        }

    children = _build_children_map(words)
    root = _find_root(words)
    main_verb = _find_main_verb(words, root, children)

    tense = _infer_tense(words, root, children)
    structure = _infer_structure(words)
    subject = _find_subject_phrase(words, children)
    verb = main_verb.text if main_verb is not None else "Unknown"
    obj = _find_object_phrase(words, children)

    ai_notes = _quiet_call(
        _generate_ai_grammar_notes,
        english_text=english_text,
        tense=tense,
        structure=structure,
        subject=subject,
        verb=verb,
        obj=obj,
    )

    if not ai_notes:
        ai_notes = [f"- {note}" for note in _default_grammar_notes(words, root, children)]

    notes: List[str] = []
    for line in ai_notes:
        line = str(line).strip()
        if line.startswith("- "):
            line = line[2:].strip()
        elif line.startswith("-"):
            line = line[1:].strip()
        if line:
            notes.append(line)

    return {
        "tense": tense,
        "structure": structure,
        "subject": subject,
        "verb": verb,
        "object": obj,
        "notes": notes,
    }


# -------------------------
# Build words array
# Output shape remains EXACTLY the same
# -------------------------
def _build_words_array(
    sentence: str,
    source_tag: str,
    target_tag: str,
    translated_target: str,
) -> List[Dict[str, Any]]:
    visible_tokens = split_text_for_meanings(sentence)
    meaning_pairs = _quiet_call(
        word_meanings,
        sentence,
        source_tag,
        target_tag,
        translated_text=translated_target,
    )

    # Precompute repeated note translations once per request
    punct_note_target = _translate_small_text(
        "Punctuation mark.",
        target_tag,
        max_new_tokens=40,
    )
    context_note_target = _translate_small_text(
        "Meaning depends on the sentence context.",
        target_tag,
        max_new_tokens=40,
    )

    pair_index = 0
    items: List[Dict[str, Any]] = []

    for i, token in enumerate(visible_tokens, start=1):
        clean = _clean_token(token)

        if _is_punctuation_only(token):
            meaning_target = token
            meaning_source = token
            pos = "PUNCT"
            note_target = punct_note_target
        else:
            meaning_target = meaning_pairs[pair_index][1] if pair_index < len(meaning_pairs) else clean
            meaning_source = clean
            pos = "WORD"
            note_target = context_note_target
            pair_index += 1

        items.append({
            "i": i,
            "raw": token,
            "clean": clean,
            "meaning_target": meaning_target,
            "meaning_source": meaning_source,
            "pos": pos,
            "note": note_target,
        })

    return items


# -------------------------
# Analyze
# Output shape remains EXACTLY the same
# -------------------------
def analyze_sentence(sentence: str, target_language: str) -> Dict[str, Any]:
    detected_lang, _ = _quiet_call(detect_language, sentence)
    source_tag = resolve_lang_code(detected_lang, text=sentence)
    target_tag = resolve_lang_code(target_language)

    translation_target = _quiet_call(
        _generate_translation,
        sentence,
        source_tag,
        target_tag,
        max_new_tokens=220,
    )

    # Keep old contract exactly the same:
    # translation_source = original sentence
    translation_source = sentence

    english_input = _get_english_sentence(
        sentence=sentence,
        source_tag=source_tag,
        target_tag=target_tag,
        translated_target=translation_target,
    )

    descriptions = _get_sentence_descriptions(
        sentence=sentence,
        source_tag=source_tag,
        target_tag=target_tag,
        translated_target=translation_target,
        english_input=english_input,
    )

    grammar = _build_grammar_object(english_input)

    words = _build_words_array(
        sentence=sentence,
        source_tag=source_tag,
        target_tag=target_tag,
        translated_target=translation_target,
    )

    return {
        "description_target": descriptions["target_description"],
        "description_source": descriptions["source_description"],
        "translation_target": translation_target,
        "translation_source": translation_source,
        "grammar": grammar,
        "words": words,
    }


# -------------------------
# Chat
# Output shape remains EXACTLY the same
# -------------------------
def chat_about_sentence(
    sentence: str,
    target_language: str,
    question: str,
    history: Optional[List[Dict[str, Any]]] = None,
) -> Dict[str, Any]:
    history = history or []

    detected_lang, _ = _quiet_call(detect_language, sentence)
    source_tag = resolve_lang_code(detected_lang, text=sentence)
    target_tag = resolve_lang_code(target_language)

    if source_tag == "eng_Latn":
        english_sentence = sentence
    else:
        english_sentence = _quiet_call(
            _generate_translation,
            sentence,
            source_tag,
            "eng_Latn",
            max_new_tokens=140,
        )

    history_text = ""
    for msg in history:
        role = (msg.get("role") or "user").upper()
        content = msg.get("content") or ""
        history_text += f"{role}: {content}\n"

    prompt = (
        "You are a subtitle tutor.\n"
        "Answer the user's question about the sentence.\n"
        "Stay focused only on this sentence.\n"
        "Be clear, accurate, and learner-friendly.\n"
        "Write the answer in English only.\n\n"
        f"Original sentence: {sentence}\n"
        f"English meaning: {english_sentence}\n\n"
        f"Previous conversation:\n{history_text}\n"
        f"User question: {question}\n\n"
        "Answer:"
    )

    answer_en = _quiet_call(
        _generate_with_explainer,
        prompt,
        max_new_tokens=220,
        keep_newlines=False,
    ).strip()

    if source_tag == "eng_Latn":
        source_answer = answer_en
    else:
        source_answer = _quiet_call(
            _generate_translation,
            answer_en,
            "eng_Latn",
            source_tag,
            max_new_tokens=220,
        )

    if target_tag == "eng_Latn":
        target_answer = answer_en
    else:
        target_answer = _quiet_call(
            _generate_translation,
            answer_en,
            "eng_Latn",
            target_tag,
            max_new_tokens=220,
        )

    reply = (
        "Source Language:\n"
        f"{source_answer}\n\n"
        "Target Language:\n"
        f"{target_answer}"
    )

    return {
        "reply": reply,
        "source_reply": source_answer,
        "target_reply": target_answer,
    }


# -------------------------
# CLI entry
# -------------------------
def main() -> None:
    try:
        if len(sys.argv) < 2:
            raise RuntimeError("Missing action. Use: analyze or chat")

        action = sys.argv[1].strip().lower()
        payload = _load_payload()

        if action == "analyze":
            sentence = (payload.get("sentence") or "").strip()
            target_language = (payload.get("targetLanguage") or "").strip()

            if not sentence or not target_language:
                raise RuntimeError("Both sentence and targetLanguage are required.")

            _ok(analyze_sentence(sentence, target_language))
            return

        if action == "chat":
            sentence = (payload.get("sentence") or "").strip()
            target_language = (payload.get("targetLanguage") or "").strip()
            question = (payload.get("question") or "").strip()
            history = payload.get("history") or []

            if not sentence or not target_language or not question:
                raise RuntimeError("sentence, targetLanguage, and question are required.")

            _ok(chat_about_sentence(sentence, target_language, question, history))
            return

        raise RuntimeError(f"Unknown action: {action}")

    except Exception as e:
        _err(str(e))
        sys.exit(1)


if __name__ == "__main__":
    main()