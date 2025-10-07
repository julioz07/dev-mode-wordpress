# 🛡️ Dev.Mode - WordPress Security Plugin

![Dev.Mode Banner](assets/images/devmode-banner.png)

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple)
![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-orange)
![Version](https://img.shields.io/badge/Version-1.1.1-green)

**🌍 Language / Idioma:**  
[![Português](https://img.shields.io/badge/🇵🇹-Português-green?style=for-the-badge)](README.md) [![English](https://img.shields.io/badge/🇺🇸-English-blue?style=for-the-badge)](README_EN.md)

**Dev.Mode** is a free WordPress plugin that provides intelligent protection by alternating between two security states: **Active** for development and **Protected** for production.

## ✨ Key Features

### 🎯 Smart States

| State | Color | Features |
|-------|-------|----------|
| **🟢 Active** | Green | ✅ Allows installations/updates<br>✅ Enables file editing<br>✅ Allows user creation |
| **🔴 Protected** | Red | 🛡️ Blocks core modifications<br>🛡️ Prevents plugin/theme installation<br>🛡️ Protects against user creation<br>🛡️ Automatic hardening |

### 🚀 Simple Interface

- **Admin Bar Toggle**: Switch states with one click
- **Settings Page**: Complete configuration in `Settings > Dev.Mode`
- **Visual Indicators**: Clear colors (green/red) to identify state
- **Security Confirmations**: Warnings before critical changes

### 🔒 Advanced Protection

- **Uploads Protection**: Blocks PHP execution in uploads folder
- **File Blocking**: Disables WordPress editors when protected
- **User Management**: Prevents unauthorized account creation
- **Auto-Revert**: Automatically returns to protected mode after X hours
- **Activity Log**: Detailed recording of all changes

## 📥 Installation

### Method 1: Direct Upload
1. Download the plugin
2. Extract to `wp-content/plugins/dev-mode/`
3. Activate in `Plugins > Installed Plugins`

### Method 2: Upload via Admin
1. Go to `Plugins > Add New > Upload Plugin`
2. Select the ZIP file
3. Click "Install Now" then "Activate"

## ⚙️ How to Use

### Switch States

**Via Admin Bar (Recommended)**
1. Click the colored indicator in the top bar
2. Confirm the change in the dialog

**Via Settings Page**
1. Go to `Settings > Dev.Mode`
2. Click the large state button
3. Configure additional options if needed

### Available Settings

- **Block User Creation**: Prevents new registrations in protected mode
- **Disable File Modifications**: Blocks updates/installations when protected
- **Protect Uploads**: Prevents PHP execution in uploads folder
- **Auto-Revert**: Sets hours to automatically return to protected mode

## 🎯 Use Cases

### 👨‍💻 For Developers
- Activate during development/testing
- Automatically protect in production
- Prevent accidental modifications

### 🏢 For Agencies
- Protect client sites
- Allow controlled access for updates
- Maintain log of all changes

### 🛡️ For Security
- Automatic hardening
- Protection against malware in uploads
- Block suspicious modifications

## 📋 Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.1 or higher  
- **Permissions**: User with `manage_options` capability
- **Server**: Apache (recommended) or IIS

## 🔍 Troubleshooting

### Plugin Doesn't Appear in Admin Bar
- Check if user has `manage_options` permissions
- Confirm plugin is active
- Clear site/browser cache

### Uploads Protection Not Working
- Check if uploads folder is writable
- Test on settings page (status section)
- Confirm server type (Apache/IIS)

### States Don't Persist
- Check if database is writable
- Temporarily disable cache plugins
- Check conflicts with other security plugins

## 🤝 Contributing

This is a **free open source** project! Contributions are very welcome:

- 🐛 **Report bugs** via [Issues](https://github.com/julioz07/dev-mode-wordpress/issues)
- 💡 **Suggest improvements** with new features
- 🔧 **Contribute code** through Pull Requests
- 🌍 **Help with translations** to other languages
- 📖 **Improve documentation**

See the [contribution guide](CONTRIBUTING.md) for more details.

## 📄 License

This plugin is licensed under [Creative Commons BY-NC-SA 4.0](LICENSE).

**Summary:**
- ✅ **Free use** for personal and non-commercial purposes
- ✅ **Modification and sharing** allowed 
- ✅ **Contributions** are welcome
- ❌ **Commercial use** without authorization
- ❌ **Commercial sale or redistribution**

## 👨‍💻 Author

**Júlio Rodrigues** - WordPress & Frontend Developer  
🌍 Portugal | 🔧 10+ years of experience  

- 🌐 **Website**: [julio-cr.pt](https://julio-cr.pt/)
- 💼 **LinkedIn**: [juliocesarrodrigues07](https://www.linkedin.com/in/juliocesarrodrigues07/)
- 🐙 **GitHub**: [julioz07](https://github.com/julioz07)

### 🤖 Developed with AI

This plugin was developed with assistance from **Claude Sonnet** (Anthropic AI), demonstrating collaboration between human development and artificial intelligence.

## 🙏 Acknowledgments

Thanks to all contributors who help make this plugin better!

---

**🔗 Useful Links**
- [Complete Documentation](README.md)
- [Contributing Guide](CONTRIBUTING.md)  
- [Report Bug](https://github.com/julioz07/dev-mode-wordpress/issues)
- [Releases](https://github.com/julioz07/dev-mode-wordpress/releases)

**Made with ❤️ for the WordPress community**