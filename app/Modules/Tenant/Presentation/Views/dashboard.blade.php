<h1>Tenant Dashboard</h1>
<p>Tenant ID: {{ tenant('id') }}</p>
<p>Data:</p>
<ul>
    @foreach($data as $key => $value)
        <li>{{ ucfirst($key) }}: {{ $value }}</li>
    @endforeach
</ul>