<h1>Te damos la bienvenida a AgroCloud</h1>
<p>Hola {{ $user->nombre_completo }}, te han invitado a {{ $user->empresa?->nombre_comercial }}.</p>
<p><a href="{{ $url }}">Crear mi contraseña y activar mi cuenta</a></p>
<p>El enlace es de un solo uso y vence en 72 horas. Tu administrador puede reenviarlo si vence.</p>
<p>Si no esperabas esta invitación, contacta al administrador de tu empresa.</p>
