#!/usr/bin/env python3
"""
build-zip.py — Crea lo ZIP del plugin Calypso Sub Arezzo per WordPress.
Uso: python3 build-zip.py [--output DIR]
"""
import argparse
import os
import re
import sys
import zipfile
from pathlib import Path

# ---------------------------------------------------------------------------
# Configurazione
# ---------------------------------------------------------------------------

PLUGIN_SLUG = "calypsosub"

# File/directory da includere nel plugin (relativi alla root del repo)
INCLUDE = [
    "calypsosub.php",
    "includes",
    "block-templates",
    "templates",
    "languages",       # opzionale, incluso se presente
]

# Pattern da escludere (glob-style, case-insensitive)
EXCLUDE_PATTERNS = [
    # Sviluppo e test
    r"tests?/",
    r"\.git/",
    r"vendor/",
    r"node_modules/",
    r"\.vscode/",
    r"\.idea/",
    # File di configurazione dev
    r"composer\.json$",
    r"composer\.lock$",
    r"phpunit\.xml",
    r"phpcs\.xml",
    r"\.phpcs",
    r"\.phpstan",
    r"\.editorconfig",
    r"\.gitignore",
    r"\.gitattributes",
    r"\.env",
    # Documentazione e design
    r"docs/",
    r"\.md$",
    r"\.html$",       # design reference
    r"build-zip\.py$",
    # Backup e file temporanei
    r"htaccess_backup",
    r"\.bak$",
    r"\.tmp$",
    r"\.log$",
    r"\.DS_Store",
    r"__MACOSX",
    r"Thumbs\.db",
]

_EXCLUDE_RE = [re.compile(p, re.IGNORECASE) for p in EXCLUDE_PATTERNS]


def should_exclude(rel_path: str) -> bool:
    """True se il file deve essere escluso dallo ZIP."""
    normalized = rel_path.replace("\\", "/")
    return any(rx.search(normalized) for rx in _EXCLUDE_RE)


def get_plugin_version(entry_point: Path) -> str:
    """Legge la versione dal header del plugin."""
    try:
        for line in entry_point.read_text(encoding="utf-8").splitlines():
            if "Version:" in line:
                return line.split("Version:")[-1].strip()
    except Exception:
        pass
    return "1.0.0"


def build_zip(repo_root: Path, output_dir: Path) -> Path:
    version = get_plugin_version(repo_root / "calypsosub.php")
    zip_name = f"{PLUGIN_SLUG}-{version}.zip"
    zip_path = output_dir / zip_name

    output_dir.mkdir(parents=True, exist_ok=True)

    added = []
    skipped = []

    with zipfile.ZipFile(zip_path, "w", compression=zipfile.ZIP_DEFLATED) as zf:
        for entry in INCLUDE:
            src = repo_root / entry
            if not src.exists():
                print(f"  [skip] {entry} — non trovato")
                continue

            if src.is_file():
                rel = f"{PLUGIN_SLUG}/{entry}"
                if should_exclude(rel):
                    skipped.append(rel)
                    continue
                zf.write(src, rel)
                added.append(rel)

            elif src.is_dir():
                for file_path in sorted(src.rglob("*")):
                    if not file_path.is_file():
                        continue
                    rel = f"{PLUGIN_SLUG}/{file_path.relative_to(repo_root).as_posix()}"
                    if should_exclude(rel):
                        skipped.append(rel)
                        continue
                    zf.write(file_path, rel)
                    added.append(rel)

    return zip_path, added, skipped


def main():
    parser = argparse.ArgumentParser(description="Build WordPress plugin ZIP")
    parser.add_argument("--output", default=".", help="Directory di output (default: cwd)")
    parser.add_argument("--list", action="store_true", help="Mostra i file inclusi senza creare lo ZIP")
    args = parser.parse_args()

    repo_root  = Path(__file__).resolve().parent
    output_dir = Path(args.output).resolve()

    print(f"Root:   {repo_root}")
    print(f"Output: {output_dir}")
    print()

    if args.list:
        print("File che verrebbero inclusi:")
        for entry in INCLUDE:
            src = repo_root / entry
            if not src.exists():
                print(f"  [missing] {entry}")
                continue
            if src.is_file():
                rel = f"{PLUGIN_SLUG}/{entry}"
                tag = "skip" if should_exclude(rel) else "ok  "
                print(f"  [{tag}] {rel}")
            elif src.is_dir():
                for fp in sorted(src.rglob("*")):
                    if not fp.is_file(): continue
                    rel = f"{PLUGIN_SLUG}/{fp.relative_to(repo_root).as_posix()}"
                    tag = "skip" if should_exclude(rel) else "ok  "
                    print(f"  [{tag}] {rel}")
        return

    zip_path, added, skipped = build_zip(repo_root, output_dir)

    print(f"Inclusi  ({len(added)}):")
    for f in added:
        print(f"  + {f}")
    if skipped:
        print(f"\nEsclusi ({len(skipped)}):")
        for f in skipped:
            print(f"  - {f}")

    size_kb = zip_path.stat().st_size / 1024
    print(f"\n✓ {zip_path.name}  ({size_kb:.1f} KB)")


if __name__ == "__main__":
    main()
