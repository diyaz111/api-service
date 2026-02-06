<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New User Registration</title>
</head>
<body>
    <h1>New user registered</h1>
    <p>A new user has been created:</p>
    <ul>
        <li><strong>Name:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Created at:</strong> {{ $user->created_at->toDateTimeString() }}</li>
    </ul>
</body>
</html>
