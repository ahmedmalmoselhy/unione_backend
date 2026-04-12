# Introduction

Comprehensive API documentation for the UniOne University Management Platform. This API provides endpoints for student enrollment, grade management, attendance tracking, announcements, and administrative functions.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

    Welcome to the UniOne API documentation. This comprehensive REST API provides all the functionality needed to manage a university system, including student enrollment, grade management, attendance tracking, announcements, and more.

    <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
    You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>

    <h3>Authentication</h3>
    <p>Most API endpoints require authentication using Laravel Sanctum tokens. Use the <code>/api/auth/login</code> endpoint to obtain a token, then include it in the Authorization header as <code>Bearer {token}</code>.</p>

