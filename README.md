# Classic Footnotes

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/abduns/classic-footnotes)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

A general footnote and reference system for adding inline source markers and a collected reference list to WordPress content.

## ✨ Features

- 🔗 **Favicon circles inline** for link sources
- 💬 **Hover/focus/tap popover** card with title, domain and snippet
- 📚 **"Sources" section** with link cards and numbered notes; back-links to citations
- ⚙️ **General settings** for automatic source appending, heading, and enabled post types
- 📝 **Classic Editor toolbar button** for inserting footnotes without typing shortcode manually
- ♿ **Accessible** (ARIA doc roles, keyboard + ESC support)
- 🌙 **Dark-mode aware** and respects reduced motion preferences
- 🚀 **No build step, no jQuery** - assets load only on posts that use footnotes

## 📦 Installation

### From WordPress Admin

1. Download the latest release
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the downloaded zip file
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin files
2. Upload the `classic-footnotes` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

## 🚀 Usage

### Inline a Link Source

```
[fn url="https://example.com" title="Example Domain"]
```

### Inline a Text Note

```
[fn text="An explanatory note shown in the popover and the list."]
```

### Link with Custom Snippet

```
[fn url="https://example.com" title="Example" text="Why this source matters"]
```

### Display Sources List

By default, the "Sources" list is appended to the end of the post automatically. To place it yourself:

```
[fn_sources]
```

### Classic Editor

In the Classic Editor:
- Use the **Reference note** toolbar button in Visual mode
- Use the **fn** Quicktags button in Text mode

All forms are self-closing, so you can freely mix link and note footnotes in any order.

## ⚙️ Configuration

Go to **Settings > Classic Footnotes** to configure:

- **Enabled Post Types**: Choose which post types support footnotes (Posts enabled by default)
- **Auto-append Sources**: Automatically add the sources list at the end of content
- **Sources Heading**: Customize the heading text for the sources section

## 🎨 How It Works

Each footnote can be:

1. **Web Link**: Shows the site favicon inline and a clickable card at the bottom
2. **Plain-text Note**: Shows a number inline and the text at the bottom

Footnotes are numbered automatically, and repeated links share the same number.

## 📋 Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the GPL-2.0+ License - see the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) file for details.

## 👤 Author

**Abdun Syakuur**

- GitHub: [@abduns](https://github.com/abduns)

## 🙏 Acknowledgments

- Favicons provided by Google's favicon service
- Built with accessibility and performance in mind

## 📝 Changelog

### 1.0.0
- Initial release
- Inline footnote markers with favicon support
- Popover cards on hover/focus/tap
- Automatic sources list generation
- Classic Editor integration
- Settings page for customization
- Accessibility features (ARIA, keyboard navigation)
- Dark mode support

---

Made with ❤️ by [Abdun Syakuur](https://github.com/abduns)