#!/usr/bin/env python3
"""Import blog docx files into local WordPress via WP-CLI."""

from __future__ import annotations

import hashlib
import json
import re
import subprocess
import sys
import time
from html import escape
from pathlib import Path

import mammoth
import requests

ROOT = Path(__file__).resolve().parent.parent
BLOGS_DIR = ROOT / "blogs"
WP_DIR = ROOT / "wordpress"
WP_CLI = ["php", "-d", "display_errors=0", "-d", "error_reporting=0", str(ROOT / "wp-cli.phar")]
IMAGES_DIR = Path(__file__).resolve().parent / "images"
IMPORT_LOG = Path(__file__).resolve().parent / "import_log.json"

PREFIX_RE = re.compile(r"^([A-Z]+\d+)_\s*(.+)\.docx$", re.IGNORECASE)

CATEGORY_IMAGES: dict[str, list[tuple[str, str]]] = {
    "Goals": [
        ("goals-mountain", "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1400&q=80&auto=format"),
        ("goals-path", "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1400&q=80&auto=format"),
        ("goals-sunrise", "https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=1400&q=80&auto=format"),
        ("goals-peak", "https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1400&q=80&auto=format"),
    ],
    "Habits": [
        ("habits-tea", "https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=1400&q=80&auto=format"),
        ("habits-journal", "https://images.unsplash.com/photo-1455393573742-6b53e9db1a46?w=1400&q=80&auto=format"),
        ("habits-walk", "https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=1400&q=80&auto=format"),
        ("habits-calm", "https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=1400&q=80&auto=format"),
    ],
    "Mindset": [
        ("mindset-sky", "https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=1400&q=80&auto=format"),
        ("mindset-ocean", "https://images.unsplash.com/photo-1439402658654-d604b0e98ef6?w=1400&q=80&auto=format"),
        ("mindset-trees", "https://images.unsplash.com/photo-1518173946687-a4c036bc65ee?w=1400&q=80&auto=format"),
        ("mindset-light", "https://images.unsplash.com/photo-1419242902214-272b3f66ee7a?w=1400&q=80&auto=format"),
    ],
    "Reflection": [
        ("reflection-desk", "https://images.unsplash.com/photo-1456324504439-367cee3b3c32?w=1400&q=80&auto=format"),
        ("reflection-pen", "https://images.unsplash.com/photo-1484480974693-6ca0a0283fcd?w=1400&q=80&auto=format"),
        ("reflection-sunset", "https://images.unsplash.com/photo-1495616811223-4d98c6e9c869?w=1400&q=80&auto=format"),
        ("reflection-window", "https://images.unsplash.com/photo-1513694203232-c359a68e27dd?w=1400&q=80&auto=format"),
    ],
}

CATEGORY_DESCRIPTIONS = {
    "Goals": "Thoughtful guidance for setting intentions that truly fit who you are becoming.",
    "Habits": "Gentle practices and daily rhythms that support a calmer, more intentional life.",
    "Mindset": "Perspectives and inner shifts for getting unstuck and choosing possibility.",
    "Reflection": "Quiet prompts and ideas for journaling, listening, and measuring a good day.",
}


def wp(*args: str, capture: bool = True) -> subprocess.CompletedProcess[str]:
    cmd = WP_CLI + list(args)
    result = subprocess.run(
        cmd,
        cwd=WP_DIR,
        capture_output=capture,
        text=True,
        encoding="utf-8",
        errors="replace",
        check=False,
    )
    if result.stdout:
        lines = [
            line
            for line in result.stdout.splitlines()
            if not line.startswith("Deprecated:") and not line.startswith("Warning:")
        ]
        result.stdout = "\n".join(lines).strip()
    return result


def slugify(text: str) -> str:
    text = text.lower().strip()
    text = re.sub(r"[^\w\s-]", "", text)
    text = re.sub(r"[\s_]+", "-", text)
    return re.sub(r"-+", "-", text).strip("-")


def parse_filename(path: Path) -> tuple[str, str]:
    match = PREFIX_RE.match(path.name)
    if not match:
        raise ValueError(f"Unexpected filename format: {path.name}")
    return match.group(1).upper(), clean_title(match.group(2).strip())


def clean_title(title: str) -> str:
    title = re.sub(r"_([^_]+)_", r"\1", title)
    title = title.replace("_", " ")
    title = re.sub(r"\s+", " ", title).strip()
    return title


def docx_to_html(path: Path) -> str:
    with path.open("rb") as handle:
        result = mammoth.convert_to_html(handle)
    if result.messages:
        for message in result.messages:
            print(f"  mammoth: {message}")
    return result.value


def is_section_heading(text: str) -> bool:
    text = re.sub(r"\s+", " ", text).strip()
    if not text or len(text) > 85:
        return False
    if re.match(r"^\d+\.\s+", text):
        return False
    if text.endswith(":"):
        return False
    if text.endswith(".") and not text.endswith("..."):
        return False
    if text.count(".") > 1:
        return False
    words = text.split()
    if len(words) > 12:
        return False
    starters = ("the ", "why ", "how ", "when ", "what ", "signs ", "if ", "you ")
    if text.lower().startswith(starters):
        return True
    if text.istitle() or (len(words) <= 6 and text[0].isupper()):
        return True
    return False


def is_step_heading(text: str) -> bool:
    return bool(re.match(r"^\d+\.\s+.+", text.strip()))


def clean_list_html(html: str) -> str:
    html = re.sub(r"<br\s*/?>\s*<br\s*/?>", "", html, flags=re.IGNORECASE)
    return html


def block_paragraph(html: str) -> str:
    inner = html.strip()
    if inner.startswith("<p"):
        inner = re.sub(r"^<p[^>]*>", "", inner, flags=re.IGNORECASE)
        inner = re.sub(r"</p>$", "", inner, flags=re.IGNORECASE)
    return (
        "<!-- wp:paragraph -->\n"
        f"<p>{inner}</p>\n"
        "<!-- /wp:paragraph -->"
    )


def block_heading(text: str, level: int = 2) -> str:
    tag = f"h{level}"
    return (
        f'<!-- wp:heading {{"level":{level}}} -->\n'
        f"<{tag}>{escape(text)}</{tag}>\n"
        f"<!-- /wp:heading -->"
    )


def block_list(html: str) -> str:
    html = clean_list_html(html)
    return (
        "<!-- wp:list -->\n"
        f"{html}\n"
        "<!-- /wp:list -->"
    )


def block_image(url: str, alt: str, attachment_id: int | None = None) -> str:
    if attachment_id:
        attrs = json.dumps({"id": attachment_id, "sizeSlug": "large", "linkDestination": "none"})
        return (
            f"<!-- wp:image {attrs} -->\n"
            f'<figure class="wp-block-image size-large"><img src="{url}" alt="{escape(alt)}"/></figure>\n'
            "<!-- /wp:image -->"
        )
    return (
        '<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->\n'
        f'<figure class="wp-block-image size-large"><img src="{url}" alt="{escape(alt)}"/></figure>\n'
        "<!-- /wp:image -->"
    )


def block_separator() -> str:
    return '<!-- wp:separator -->\n<hr class="wp-block-separator has-alpha-channel-opacity"/>\n<!-- /wp:separator -->'


def html_to_blocks(html: str, inline_image: tuple[str, int, str] | None) -> str:
    chunks = re.split(r"(<ul>.*?</ul>)", html, flags=re.DOTALL | re.IGNORECASE)
    blocks: list[str] = []
    heading_count = 0

    for chunk in chunks:
        chunk = chunk.strip()
        if not chunk:
            continue
        if chunk.lower().startswith("<ul"):
            blocks.append(block_list(chunk))
            continue

        paragraphs = re.findall(r"<p[^>]*>.*?</p>", chunk, flags=re.DOTALL | re.IGNORECASE)
        if not paragraphs:
            continue

        for paragraph in paragraphs:
            text = re.sub(r"<[^>]+>", "", paragraph)
            text = re.sub(r"\s+", " ", text).strip()
            if is_step_heading(text):
                blocks.append(block_heading(text, level=3))
                heading_count += 1
            elif is_section_heading(text):
                blocks.append(block_heading(text, level=2))
                heading_count += 1
                if inline_image and heading_count % 2 == 0:
                    url, attachment_id, alt = inline_image
                    blocks.append(block_image(url, alt, attachment_id))
                    blocks.append(block_separator())
            else:
                blocks.append(block_paragraph(paragraph))

    return "\n\n".join(blocks)


def pick_images(category: str, seed: str) -> tuple[tuple[str, str], tuple[str, str]]:
    pool = CATEGORY_IMAGES[category]
    digest = int(hashlib.md5(seed.encode("utf-8")).hexdigest(), 16)
    featured = pool[digest % len(pool)]
    inline = pool[(digest + 3) % len(pool)]
    return featured, inline


def download_image(name: str, url: str) -> Path:
    IMAGES_DIR.mkdir(parents=True, exist_ok=True)
    target = IMAGES_DIR / f"{name}.jpg"
    if target.exists() and target.stat().st_size > 1000:
        return target

    fallback = f"https://picsum.photos/seed/{name}/1400/900"
    for candidate in (url, fallback):
        try:
            response = requests.get(candidate, timeout=60)
            response.raise_for_status()
            target.write_bytes(response.content)
            return target
        except requests.RequestException:
            continue
    raise RuntimeError(f"Unable to download image for {name}")


def import_media(path: Path, title: str) -> tuple[int, str]:
    result = wp("media", "import", str(path), f"--title={title}", "--porcelain")
    if result.returncode != 0:
        raise RuntimeError(result.stderr or result.stdout)
    attachment_id = int(result.stdout.strip())
    url_result = wp("post", "get", str(attachment_id), "--field=url")
    if url_result.returncode != 0:
        raise RuntimeError(url_result.stderr or url_result.stdout)
    return attachment_id, url_result.stdout.strip()


def ensure_category(name: str) -> int:
    slug = slugify(name)
    lookup = wp("term", "list", "category", f"--slug={slug}", "--field=term_id")
    if lookup.returncode == 0 and lookup.stdout.strip().isdigit():
        term_id = int(lookup.stdout.strip())
        wp("term", "update", "category", str(term_id), f"--description={CATEGORY_DESCRIPTIONS[name]}")
        return term_id

    create = wp(
        "term",
        "create",
        "category",
        name,
        f"--slug={slug}",
        f"--description={CATEGORY_DESCRIPTIONS[name]}",
        "--porcelain",
    )
    if create.returncode != 0:
        retry = wp("term", "list", "category", f"--slug={slug}", "--field=term_id")
        if retry.returncode == 0 and retry.stdout.strip().isdigit():
            return int(retry.stdout.strip())
        raise RuntimeError(create.stderr or create.stdout)
    return int(create.stdout.strip())


def create_or_update_post(
    *,
    title: str,
    content: str,
    category_id: int,
    featured_id: int,
    code: str,
) -> int:
    slug = slugify(title)
    content_path = Path(__file__).resolve().parent / "tmp" / f"{slug}.html"
    content_path.parent.mkdir(parents=True, exist_ok=True)
    content_path.write_text(content, encoding="utf-8")

    php_path = Path(__file__).resolve().parent / "tmp" / f"{slug}.php"
    php_path.write_text(
        f"""<?php
$content = file_get_contents( {json.dumps(str(content_path))} );
$slug = {json.dumps(slug)};
$title = {json.dumps(title)};
$category_id = {category_id};
$featured_id = {featured_id};
$code = {json.dumps(code)};

$existing = get_page_by_path( $slug, OBJECT, 'post' );
$data = array(
    'post_title'   => $title,
    'post_name'    => $slug,
    'post_content' => $content,
    'post_status'  => 'publish',
    'post_type'    => 'post',
);

if ( $existing ) {{
    $data['ID'] = $existing->ID;
    $post_id = wp_update_post( $data, true );
}} else {{
    $post_id = wp_insert_post( $data, true );
}}

if ( is_wp_error( $post_id ) ) {{
    WP_CLI::error( $post_id->get_error_message() );
}}

wp_set_post_terms( $post_id, array( $category_id ), 'category', false );
update_post_meta( $post_id, '_thumbnail_id', $featured_id );
update_post_meta( $post_id, '_ml_post_code', $code );
WP_CLI::line( (string) $post_id );
""",
        encoding="utf-8",
    )

    result = wp("eval-file", str(php_path))
    if result.returncode != 0:
        raise RuntimeError(result.stderr or result.stdout)
    post_id = int(result.stdout.strip().splitlines()[-1])
    return post_id


def import_all() -> list[dict]:
    files = sorted(BLOGS_DIR.glob("*/*.docx"))
    if not files:
        raise SystemExit(f"No docx files found in {BLOGS_DIR}")

    category_ids = {name: ensure_category(name) for name in CATEGORY_DESCRIPTIONS}
    imported: list[dict] = []

    for index, path in enumerate(files, start=1):
        category = path.parent.name
        code, title = parse_filename(path)
        print(f"[{index}/{len(files)}] {code} - {title}")

        featured_meta, inline_meta = pick_images(category, code)
        featured_path = download_image(f"{code}-featured-{featured_meta[0]}", featured_meta[1])
        inline_path = download_image(f"{code}-inline-{inline_meta[0]}", inline_meta[1])

        featured_id, _ = import_media(featured_path, f"{title} - featured")
        inline_id, inline_url = import_media(inline_path, f"{title} - inline")

        html = docx_to_html(path)
        content = html_to_blocks(html, (inline_url, inline_id, title))
        post_id = create_or_update_post(
            title=title,
            content=content,
            category_id=category_ids[category],
            featured_id=featured_id,
            code=code,
        )
        imported.append(
            {
                "code": code,
                "title": title,
                "category": category,
                "post_id": post_id,
                "slug": slugify(title),
            }
        )
        time.sleep(0.2)

    IMPORT_LOG.write_text(json.dumps(imported, indent=2), encoding="utf-8")
    return imported


if __name__ == "__main__":
    try:
        posts = import_all()
        print(f"\nImported {len(posts)} posts.")
    except Exception as exc:
        print(f"Import failed: {exc}", file=sys.stderr)
        raise
