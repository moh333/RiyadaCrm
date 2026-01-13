<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Tenant</title>
</head>
<body>
    <h1>Create Tenant</h1>

    @if($errors->any())
        <div style="color:red">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('tenants.store') }}">
        @csrf
        <div>
            <label>Name</label>
            <input name="name" value="{{ old('name') }}" required />
        </div>
        <div>
            <label>DB Name</label>
            <input name="db_name" value="{{ old('db_name') }}" required />
        </div>
        <div>
            <label>DB Host (optional)</label>
            <input name="db_host" value="{{ old('db_host', env('DB_HOST')) }}" />
        </div>
        <div>
            <label>DB Username (optional)</label>
            <input name="db_username" value="{{ old('db_username', env('DB_USERNAME')) }}" />
        </div>
        <div>
            <label>DB Password (optional)</label>
            <input name="db_password" value="" />
        </div>

        <div>
            <button type="submit">Create</button>
        </div>
    </form>

    <p><a href="{{ route('tenants.index') }}">Back to list</a></p>
</body>
</html>
