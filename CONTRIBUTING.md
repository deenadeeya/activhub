# Contributing to ActivHub

Thank you for your interest in contributing to ActivHub! This document provides guidelines and information for contributors.

## 🤝 Ways to Contribute

### Bug Reports
- Use the [GitHub Issues](../../issues) to report bugs
- Include detailed reproduction steps
- Provide system information (PHP version, browser, OS)
- Include screenshots if applicable

### Feature Requests
- Submit feature requests via [GitHub Issues](../../issues)
- Clearly describe the proposed feature
- Explain the use case and benefits
- Consider implementation complexity

### Code Contributions
- Fork the repository
- Create a feature branch
- Follow coding standards
- Submit a pull request

## 🔧 Development Setup

### Prerequisites
- PHP 8.0+
- MySQL/MariaDB
- Composer
- Git

### Local Development
1. Fork and clone the repository
2. Install dependencies: `composer install`
3. Set up local database
4. Configure `config/connect.php`
5. Start local development server

## 📝 Coding Standards

### PHP Standards
- Follow PSR-12 coding standard
- Use meaningful variable and function names
- Add PHPDoc comments for functions
- Validate and sanitize all inputs
- Use prepared statements for database queries

### File Organization
- Place admin functions in `admin/function/`
- Use appropriate directory structure
- Keep functions modular and reusable
- Separate concerns properly

### Security Guidelines
- Always validate user input
- Use prepared statements
- Implement proper authentication checks
- Follow principle of least privilege
- Sanitize output to prevent XSS

## 🧪 Testing Guidelines

### Manual Testing
- Test all user roles (student, teacher, admin)
- Verify notification system functionality
- Test file upload and import features
- Check responsive design on different devices

### Database Testing
- Test with realistic data volumes
- Verify data integrity constraints
- Test backup and restore procedures

## 📋 Pull Request Process

### Before Submitting
1. Ensure code follows project standards
2. Test thoroughly on local environment
3. Update documentation if needed
4. Check for any security vulnerabilities

### PR Description
- Clearly describe changes made
- Reference related issues
- Include screenshots for UI changes
- List any breaking changes

### Review Process
1. Automated checks must pass
2. Code review by maintainers
3. Testing by reviewers
4. Approval and merge

## 🎯 Priority Areas

### High Priority
- Security improvements
- Performance optimizations
- Bug fixes
- Accessibility improvements

### Medium Priority
- New features
- UI/UX enhancements
- Documentation improvements
- Test coverage

### Low Priority
- Code refactoring
- Minor UI tweaks
- Nice-to-have features

## 📚 Documentation

### Required Documentation
- Update README.md for new features
- Document API changes
- Update installation guide if needed
- Include inline code comments

### Documentation Style
- Use clear, concise language
- Include code examples
- Provide context and rationale
- Keep documentation up-to-date

## 🐛 Bug Report Template

```markdown
**Bug Description**
A clear description of the bug.

**Steps to Reproduce**
1. Go to '...'
2. Click on '...'
3. See error

**Expected Behavior**
What you expected to happen.

**Actual Behavior**
What actually happened.

**Environment**
- PHP Version:
- Browser:
- OS:
- Database:

**Screenshots**
If applicable, add screenshots.

**Additional Context**
Any other context about the problem.
```

## 🚀 Feature Request Template

```markdown
**Feature Description**
A clear description of the requested feature.

**Use Case**
Describe the problem this feature would solve.

**Proposed Solution**
Describe your proposed solution.

**Alternative Solutions**
Describe alternatives you've considered.

**Additional Context**
Any other context or screenshots.
```

## 🔒 Security Policy

### Reporting Security Vulnerabilities
- Email security issues to: [security-email]
- Do not create public issues for security vulnerabilities
- Allow reasonable time for response and patching

### Security Best Practices
- Keep dependencies updated
- Follow OWASP guidelines
- Implement proper input validation
- Use secure coding practices

## 📞 Getting Help

### Resources
- [Project Documentation](README.md)
- [Installation Guide](INSTALLATION.md)
- [Changelog](CHANGELOG.md)

### Community
- GitHub Discussions for questions
- GitHub Issues for bugs and features
- Email maintainers for urgent matters

## 🎖️ Recognition

Contributors will be recognized in:
- Project README.md
- Release notes
- Contributor list

## 📜 License

By contributing to ActivHub, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to ActivHub! Your efforts help improve education technology for SRI AL-AMIN students and educators.
