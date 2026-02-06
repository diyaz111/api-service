<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Created</title>
</head>
<body>
    <h1>Welcome, {{ $user->name }}!</h1>
    <p>Your account has been successfully created.</p>
    <p>Email: {{ $user->email }}</p>
    <p>You can now log in and start using the service.</p>
</body>
</html>
