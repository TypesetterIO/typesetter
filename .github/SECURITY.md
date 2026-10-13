# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.x     | Yes       |
| < 1.0   | No        |

## Reporting a Vulnerability

Report vulnerabilities privately through
[GitHub's vulnerability reporting form](https://github.com/TypesetterIO/typesetter/security/advisories/new).
Do not open a public issue.

Include the Typesetter version, the PHP version, and the smallest config and
content that reproduce the problem.

You will get a response within 7 days. Confirmed vulnerabilities are fixed in a
new release and published as a GitHub security advisory, crediting the reporter
unless they ask otherwise.

## Scope

Typesetter treats the config, theme, and content it is given as trusted input
written by the book's author. Behavior that requires a malicious config, theme,
or Markdown file is not a vulnerability.

Report vulnerabilities in dependencies such as mPDF or league/commonmark to
those projects, unless the problem is in how Typesetter uses them.
