#!/usr/bin/env python3
"""
Replace Font Awesome icon tags in Blade templates with Material Symbols
<x-global::elements.icon> component calls.

Usage:
    python3 scripts/replace-fa-icons.py app/Domain/Tickets/Templates/
    python3 scripts/replace-fa-icons.py app/Domain/Tickets/Templates/ --dry-run
    python3 scripts/replace-fa-icons.py app/Domain/Tickets/Templates/ --verbose
"""

import argparse
import json
import os
import re
import sys
from pathlib import Path
from typing import Optional


def load_mapping(mapping_path: str) -> dict:
    """Load icon-mapping.json and return the mapping dict."""
    with open(mapping_path, "r") as f:
        data = json.load(f)
    return data["mapping"]


# FA prefix patterns
FA_PREFIXES = {"fa", "fas", "far", "fab", "fal", "fa-solid", "fa-regular", "fa-brands", "fa-light"}

# FA utility classes to strip
FA_UTILITY_CLASSES = {
    "fa", "fas", "far", "fab", "fal",
    "fa-solid", "fa-regular", "fa-brands", "fa-light",
    "fa-fw", "fa-spin", "fa-pulse",
    "fa-lg", "fa-2x", "fa-3x", "fa-4x", "fa-5x",
    "fa-flip-horizontal", "fa-flip-vertical",
    "fa-rotate-90", "fa-rotate-180", "fa-rotate-270",
    "fa-inverse", "fa-stack", "fa-stack-1x", "fa-stack-2x",
    "fa-border", "fa-pull-left", "fa-pull-right",
    "fa-li", "fa-ul", "fa-fixed-width",
}


def find_tag_end(content: str, start: int) -> int:
    """Find the end of an HTML tag starting at '<', handling Blade expressions.

    Returns the index after the closing '>' (or after '</tag>').
    Handles {{ }}, {!! !!}, and quoted attributes containing Blade expressions.
    """
    i = start
    length = len(content)

    # Find the tag name
    i += 1  # skip '<'
    while i < length and content[i] in " \t\n\r":
        i += 1

    tag_start = i
    while i < length and content[i] not in " \t\n\r>/":
        i += 1
    tag_name = content[tag_start:i].lower()

    # Find the closing > of the opening tag
    in_single_quote = False
    in_double_quote = False
    self_closing = False

    while i < length:
        ch = content[i]

        # Handle Blade expressions inside attributes
        if not in_single_quote and not in_double_quote:
            if content[i:i+2] == '{{':
                # Skip to closing }}
                j = content.find('}}', i + 2)
                if j != -1:
                    i = j + 2
                    continue
            if content[i:i+3] == '{!!':
                # Skip to closing !!}
                j = content.find('!!}', i + 3)
                if j != -1:
                    i = j + 3
                    continue

        if ch == '"' and not in_single_quote:
            in_double_quote = not in_double_quote
        elif ch == "'" and not in_double_quote:
            in_single_quote = not in_single_quote
        elif ch == '/' and not in_double_quote and not in_single_quote:
            self_closing = True
        elif ch == '>' and not in_double_quote and not in_single_quote:
            open_end = i + 1
            break
        i += 1
    else:
        return -1  # malformed tag

    if self_closing:
        return open_end

    # Look for optional closing tag (</i> or </span>)
    # Allow whitespace between opening and closing tags
    j = open_end
    while j < length and content[j] in " \t\n\r":
        j += 1

    close_pattern = f"</{tag_name}>"
    if j + len(close_pattern) <= length and content[j:j+len(close_pattern)].lower() == close_pattern:
        return j + len(close_pattern)

    return open_end


def extract_class_value(attrs_str: str) -> tuple:
    """Extract the class attribute value from an attributes string.

    Returns (class_value, attrs_without_class).
    Handles Blade expressions inside attribute values.
    """
    # Find class=" in the attributes
    match = re.search(r'\bclass\s*=\s*"', attrs_str)
    if not match:
        # Try single quotes
        match = re.search(r"\bclass\s*=\s*'", attrs_str)
        if not match:
            return None, attrs_str

    quote_char = attrs_str[match.end() - 1]
    class_start = match.start()
    value_start = match.end()

    # Find the closing quote, handling Blade expressions
    i = value_start
    while i < len(attrs_str):
        if attrs_str[i:i+2] == '{{':
            j = attrs_str.find('}}', i + 2)
            if j != -1:
                i = j + 2
                continue
        if attrs_str[i:i+3] == '{!!':
            j = attrs_str.find('!!}', i + 3)
            if j != -1:
                i = j + 3
                continue
        if attrs_str[i] == quote_char:
            break
        i += 1

    class_value = attrs_str[value_start:i]
    # Remove class attribute from string
    attrs_without = attrs_str[:class_start] + attrs_str[i+1:]

    return class_value, attrs_without


def parse_fa_tag(content: str, start: int) -> Optional[dict]:
    """Parse an <i> or <span> tag at the given position.

    Returns dict with: tag, icon_name, extra_classes, extra_attrs, full_match_end
    Or None if not an FA icon tag.
    """
    tag_end = find_tag_end(content, start)
    if tag_end == -1:
        return None

    full_match = content[start:tag_end]

    # Get the tag name
    tag_match = re.match(r'<(i|span)\s', full_match, re.IGNORECASE)
    if not tag_match:
        return None

    tag_name = tag_match.group(1).lower()

    # Extract everything between <tag and > (the attributes region)
    # Find the first > that closes the opening tag
    open_tag_content = full_match
    # Check for closing tag
    close_tag = f"</{tag_name}>"
    if open_tag_content.lower().endswith(close_tag):
        open_tag_content = open_tag_content[:-len(close_tag)]

    # Strip the <tag and trailing > or />
    inner = re.sub(r'^<' + tag_name + r'\s+', '', open_tag_content, flags=re.IGNORECASE)
    inner = re.sub(r'\s*/?\s*>$', '', inner)

    # Extract class value
    class_value, remaining_attrs = extract_class_value(inner)
    if class_value is None:
        return None

    # Check if this has FA icon classes
    classes = class_value.split()
    icon_name = None
    extra_classes = []
    has_fa_prefix = False

    for cls in classes:
        low = cls.lower()
        if low in FA_UTILITY_CLASSES or low in FA_PREFIXES:
            has_fa_prefix = True
            continue
        if low.startswith("fa-"):
            # This is the icon name
            icon_name = low[3:]  # strip "fa-"
            continue
        # Non-FA class — keep it
        extra_classes.append(cls)

    if not has_fa_prefix or icon_name is None:
        return None

    # Clean up remaining attributes
    remaining_attrs = remaining_attrs.strip()
    # Remove aria-hidden="true"
    remaining_attrs = re.sub(r'\s*aria-hidden\s*=\s*["\']true["\']\s*', ' ', remaining_attrs)
    remaining_attrs = remaining_attrs.strip()

    return {
        "tag": tag_name,
        "icon_name": icon_name,
        "extra_classes": extra_classes,
        "extra_attrs": remaining_attrs,
        "match_start": start,
        "match_end": tag_end,
        "full_match": content[start:tag_end],
    }


def build_replacement(material_name: str, extra_classes: list, extra_attrs: str) -> str:
    """Build the <x-global::elements.icon> replacement tag."""
    parts = [f'<x-global::elements.icon name="{material_name}"']

    if extra_classes:
        parts.append(f'class="{" ".join(extra_classes)}"')

    if extra_attrs:
        parts.append(extra_attrs)

    return " ".join(parts) + " />"


def should_skip_context(content: str, pos: int) -> bool:
    """Check if a position is inside a JS block or other skip context."""
    # Get context before this position
    ctx_start = max(0, pos - 1000)
    before = content[ctx_start:pos]

    # Skip if inside a <script> block
    last_script_open = before.rfind("<script")
    last_script_close = before.rfind("</script")
    if last_script_open > last_script_close:
        return True

    # Skip if inside a JS object/config (bootstrapFontAwesome)
    lines_before = before.split("\n")
    for line in reversed(lines_before[-10:]):
        if "bootstrapFontAwesome" in line or "fontAwesome" in line:
            return True

    # Skip if inside a Blade comment {{-- --}}
    last_comment_open = before.rfind("{{--")
    last_comment_close = before.rfind("--}}")
    if last_comment_open > last_comment_close:
        return True

    # Skip if inside a PHP comment block
    last_php_comment_open = before.rfind("<?php /*")
    last_php_comment_close = before.rfind("*/")
    if last_php_comment_open > last_php_comment_close:
        return True

    return False


def find_fa_tags(content: str):
    """Find all <i> and <span> tags with FA classes in content.

    Yields (start_pos, tag_info) tuples in reverse order (for safe replacement).
    """
    # Find all <i or <span tags
    tag_pattern = re.compile(r'<(i|span)\s', re.IGNORECASE)
    matches = []

    for m in tag_pattern.finditer(content):
        # Quick check: is "fa" somewhere nearby?
        preview = content[m.start():min(m.start() + 500, len(content))]
        if not re.search(r'(?:^|[\s"\'])(?:fa|fas|far|fab|fal|fa-solid|fa-regular|fa-brands)\s+fa-', preview):
            continue

        tag_info = parse_fa_tag(content, m.start())
        if tag_info:
            matches.append(tag_info)

    # Return in reverse order for safe replacement
    matches.reverse()
    return matches


def process_file(
    filepath: str,
    mapping: dict,
    dry_run: bool = False,
    verbose: bool = False,
) -> dict:
    """Process a single Blade file, replacing FA icons with Material Symbols."""
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    original = content
    stats = {"replaced": 0, "skipped": 0, "unmapped": []}

    tags = find_fa_tags(content)

    for tag_info in tags:
        icon_name = tag_info["icon_name"]
        start = tag_info["match_start"]
        end = tag_info["match_end"]

        # Check if we should skip this context
        if should_skip_context(content, start):
            stats["skipped"] += 1
            if verbose:
                print(f"  SKIP (context): fa-{icon_name} at line {content[:start].count(chr(10)) + 1}")
            continue

        # Look up Material Symbols name
        material_name = mapping.get(icon_name)
        if material_name is None:
            stats["unmapped"].append(icon_name)
            if verbose:
                print(f"  UNMAPPED: fa-{icon_name} at line {content[:start].count(chr(10)) + 1}")
            continue

        # Build replacement
        replacement = build_replacement(
            material_name,
            tag_info["extra_classes"],
            tag_info["extra_attrs"],
        )

        if verbose:
            old_text = tag_info["full_match"]
            # Truncate long matches for display
            if len(old_text) > 120:
                old_text = old_text[:120] + "..."
            print(f"  REPLACE: {old_text.strip()}")
            print(f"     WITH: {replacement}")

        # Replace in content
        content = content[:start] + replacement + content[end:]
        stats["replaced"] += 1

    if content != original and not dry_run:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)

    return stats


def process_directory(
    directory: str,
    mapping: dict,
    dry_run: bool = False,
    verbose: bool = False,
) -> dict:
    """Process all .blade.php files in a directory tree."""
    total_stats = {
        "files_changed": 0,
        "total_replaced": 0,
        "total_skipped": 0,
        "total_unmapped": [],
        "files_processed": 0,
    }

    blade_files = sorted(Path(directory).rglob("*.blade.php"))

    for filepath in blade_files:
        filepath_str = str(filepath)

        # Quick check: does the file contain FA references?
        with open(filepath_str, "r", encoding="utf-8") as f:
            content = f.read()

        if not re.search(
            r'(?:fa(?:-solid|-regular|-brands|-light)?|fas|far|fab|fal)\s+fa-',
            content,
            re.IGNORECASE,
        ):
            continue

        total_stats["files_processed"] += 1
        rel_path = os.path.relpath(filepath_str, os.getcwd())

        if verbose or dry_run:
            print(f"\nProcessing: {rel_path}")

        stats = process_file(filepath_str, mapping, dry_run, verbose)

        if stats["replaced"] > 0:
            total_stats["files_changed"] += 1
            total_stats["total_replaced"] += stats["replaced"]
            action = "Would replace" if dry_run else "Replaced"
            print(f"  {action} {stats['replaced']} icon(s) in {rel_path}")

        total_stats["total_skipped"] += stats["skipped"]
        total_stats["total_unmapped"].extend(stats["unmapped"])

    return total_stats


def main():
    parser = argparse.ArgumentParser(
        description="Replace FA icons in Blade templates with Material Symbols"
    )
    parser.add_argument(
        "directory",
        help="Directory to process (e.g., app/Domain/Tickets/Templates/)",
    )
    parser.add_argument(
        "--mapping",
        default="icon-mapping.json",
        help="Path to icon-mapping.json (default: icon-mapping.json)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Show what would be changed without modifying files",
    )
    parser.add_argument(
        "--verbose",
        action="store_true",
        help="Show detailed replacement information",
    )

    args = parser.parse_args()

    if not os.path.isdir(args.directory):
        print(f"Error: {args.directory} is not a directory", file=sys.stderr)
        sys.exit(1)

    mapping = load_mapping(args.mapping)
    print(f"Loaded {len(mapping)} icon mappings")
    print(f"Processing: {args.directory}")
    if args.dry_run:
        print("DRY RUN - no files will be modified\n")

    stats = process_directory(args.directory, mapping, args.dry_run, args.verbose)

    print(f"\n{'=' * 50}")
    print(f"Files processed: {stats['files_processed']}")
    print(f"Files changed:   {stats['files_changed']}")
    print(f"Icons replaced:  {stats['total_replaced']}")
    print(f"Icons skipped:   {stats['total_skipped']} (context skip)")

    if stats["total_unmapped"]:
        unique_unmapped = sorted(set(stats["total_unmapped"]))
        print(f"Unmapped icons:  {len(unique_unmapped)}")
        for icon in unique_unmapped:
            count = stats["total_unmapped"].count(icon)
            print(f"  - fa-{icon} ({count}x)")


if __name__ == "__main__":
    main()
