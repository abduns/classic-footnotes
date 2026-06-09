=== Classic Footnotes ===
Contributors: abdunsyakuur
Tags: footnotes, citations, sources, references, shortcode
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A general footnote and reference system for adding inline source markers and a collected reference list to WordPress content.

== Description ==

Classic Footnotes helps you add inline references, web sources, and explanatory footnotes to WordPress content. It displays compact inline markers in the article and collects every source into a reference list for readers.

Each footnote can be a **web link** (shows the site favicon inline and a clickable card at the
bottom) or a **plain-text note** (shows a number inline and the text at the bottom). Footnotes are
added with a single `[fn]` shortcode and are numbered automatically; repeated links share a number.

= Usage =

Inline a link source:

`[fn url="https://example.com" title="Example Domain"]`

Inline a text note:

`[fn text="An explanatory note shown in the popover and the list."]`

A link with a custom snippet (shown in the popover):

`[fn url="https://example.com" title="Example" text="Why this source matters"]`

In the Classic Editor, use the Reference note toolbar button in Visual mode, or the `fn`
Quicktags button in Text mode, to insert a footnote without typing the shortcode manually.

All forms are self-closing, so you can freely mix link and note footnotes in any order. (An
enclosed form, `[fn]note text[/fn]`, also works for a single note, but the self-closing `text="..."`
attribute is recommended — it never collides when a link footnote precedes a note footnote.)

By default the "Sources" list is appended to the end of the post automatically. To place it
yourself, drop `[fn_sources]` where you want it.

= Features =

* Favicon circles inline for link sources.
* Hover / focus / tap popover card with the title, domain and snippet.
* "Sources" section with link cards and numbered notes; back-links to the citation.
* General settings for automatic source appending, the heading, and enabled post types.
* Classic Editor toolbar button for inserting footnotes without typing the shortcode manually.
* Accessible (ARIA doc roles, keyboard + ESC), dark-mode aware, respects reduced motion.
* No build step, no jQuery; assets load only on posts that use a footnote.

== Frequently Asked Questions ==

= Where do favicons come from? =

From Google's favicon service. Plain-text notes use numbered markers.

= Which post types are supported? =

Posts are enabled by default. You can change enabled post types in Settings > Classic Footnotes.

== Changelog ==

= 1.0.0 =
* Initial release.
