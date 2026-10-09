@if (session('success'))
    <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
@endif
@if ($errors->any())
    <x-ui.alert type="error">
        <p class="font-semibold">Revisa los datos ingresados.</p>
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
