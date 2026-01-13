<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tenants</title>
</head>
<body>
    <h1>Tenants</h1>

    @if(session('success'))
        <div style="color:green">{{ session('success') }}</div>
    @endif

    <p><a href="{{ route('tenants.create') }}">Create tenant</a></p>

    <table border="1" cellpadding="6">
        <thead>
            <tr><th>ID</th><th>Name</th><th>DB Name</th><th>Created</th></tr>
        </thead>
        <tbody>
            @foreach($tenants as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->db_name }}</td>
                    <td>{{ $t->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
