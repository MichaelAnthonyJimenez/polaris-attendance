#!/usr/bin/env python3
import argparse
import json
import sys


def out(payload):
    print(json.dumps(payload, ensure_ascii=False))


def run_easyocr(image_path, lang):
    try:
        import easyocr
    except Exception as e:
        out({"status": "error", "reason": "easyocr_not_installed", "message": str(e)})
        return 1

    try:
        reader = easyocr.Reader([lang], gpu=False)
        chunks = reader.readtext(image_path, detail=0, paragraph=True)
        raw_text = "\n".join([str(c).strip() for c in chunks if str(c).strip()])
        out({"status": "ok", "raw_text": raw_text})
        return 0
    except Exception as e:
        out({"status": "error", "reason": "easyocr_failed", "message": str(e)})
        return 1


def run_paddleocr(image_path, lang):
    try:
        from paddleocr import PaddleOCR
    except Exception as e:
        out({"status": "error", "reason": "paddleocr_not_installed", "message": str(e)})
        return 1

    try:
        ocr = PaddleOCR(use_angle_cls=True, lang=lang)
        result = ocr.ocr(image_path, cls=True)
        texts = []
        for line in result or []:
            for item in line or []:
                if isinstance(item, (list, tuple)) and len(item) >= 2:
                    det = item[1]
                    if isinstance(det, (list, tuple)) and len(det) >= 1:
                        t = str(det[0]).strip()
                        if t:
                            texts.append(t)
        raw_text = "\n".join(texts)
        out({"status": "ok", "raw_text": raw_text})
        return 0
    except Exception as e:
        out({"status": "error", "reason": "paddleocr_failed", "message": str(e)})
        return 1


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--image", required=True)
    parser.add_argument("--engine", choices=["easyocr", "paddleocr"], required=True)
    parser.add_argument("--lang", default="en")
    args = parser.parse_args()

    if args.engine == "easyocr":
        return run_easyocr(args.image, args.lang)
    return run_paddleocr(args.image, args.lang)


if __name__ == "__main__":
    sys.exit(main())

