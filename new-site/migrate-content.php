<?php
/**
 * Migra el contenido curado desde legacy-site/database/ a páginas de WordPress
 * en el sitio nuevo (bloques nativos, tema instituto-13-de-julio).
 *
 * Uso: correr desde la raíz del core de WordPress instalado localmente
 * (ver new-site/DEV_SETUP.md):
 *
 *   php ../new-site/migrate-content.php
 *
 * Es idempotente: si una página con ese post_name ya existe, la actualiza
 * en vez de duplicarla.
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( "Este script se corre por CLI.\n" );
}

require_once getcwd() . '/wp-load.php';

function i13j_upsert_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );

	$postarr = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		wp_update_post( $postarr );
		echo "Actualizada: /{$slug}/ (ID {$existing->ID})\n";
	} else {
		$id = wp_insert_post( $postarr );
		echo "Creada: /{$slug}/ (ID {$id})\n";
	}
}

// ---------------------------------------------------------------------
// Historia — texto real desde legacy-site (post_name=historia, ID 446)
// ---------------------------------------------------------------------
i13j_upsert_page(
	'historia',
	'Historia',
	<<<'HTML'
<!-- wp:paragraph {"fontFamily":"mono","textColor":"neutral","style":{"typography":{"fontSize":"0.8rem","letterSpacing":"0.1em","textTransform":"uppercase"}}} -->
<p class="has-neutral-color has-text-color has-mono-font-family" style="font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase">Desde 1944</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>El Instituto de Educación Técnica y Formación Profesional &#8220;13 de Julio&#8221; fue creado en 1944 como la Escuela de Aprendices de la Compañía Argentina de Electricidad (CADE). En 1960 pasó a ser la ENET SEGBA, que en 1966, por su crecimiento, incorporó el Ciclo Superior que otorga el título de Técnico.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>La Escuela ha sido un referente de la educación técnica para el mercado laboral eléctrico: sus egresados logran una inserción laboral inmediata o la continuidad, con éxito, de estudios superiores.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Como consecuencia de las privatizaciones, la ENET SEGBA dejó de funcionar con ese nombre. El Sindicato de Luz y Fuerza &#8211; Capital Federal creó entonces una Asociación Civil sin fines de lucro para darle continuidad al establecimiento educativo, que desde el 1° de enero de 1993 se denomina Instituto de Educación Técnica y Formación Profesional &#8220;13 de Julio&#8221;.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Hoy, el título de Técnico Mecánico Electricista sigue siendo un puente hacia los estudios superiores, la autogestión y el trabajo en empresas del sector. El Instituto está abierto a toda la comunidad y continúa ofreciendo una propuesta educativa acorde a las exigencias del desarrollo técnico y social, sin dejar de lado el factor humano y la solidaridad.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"8px"}}} -->
<figure class="wp-block-image size-large has-custom-border"><img src="/wp-content/themes/instituto-13-de-julio/assets/images/taller-torno.jpg" alt="Estudiante trabajando en el taller de torno del Instituto" style="border-radius:8px"/></figure>
<!-- /wp:image -->
HTML
);

// ---------------------------------------------------------------------
// Cuerpo Directivo — listado real desde legacy-site (post_name=cuerpo-directivo, ID 513)
// ---------------------------------------------------------------------
i13j_upsert_page(
	'cuerpo-directivo',
	'Cuerpo Directivo',
	<<<'HTML'
<!-- wp:list -->
<ul class="wp-block-list">
<li><strong>Rectora</strong> — Prof. Lic. Carla Tomietto</li>
<li><strong>Director/a de Estudios</strong> — TM: Prof. Lic. Victoria Coga · TT: Prof. Lic. Andrés Castro</li>
<li><strong>Secretaria</strong> — Prof. Lic. Belén Requejo</li>
<li><strong>Pro Secretaria</strong> — Prof. Lorena Gerardi</li>
<li><strong>Asesor pedagógico</strong> — Prof. Lic. Nahuel Karapen</li>
<li><strong>Regentes</strong> — TM: Prof. Ing. Mariano Sciolla · TT: Prof. Francisco Tolaba</li>
<li><strong>Gabinete</strong> — TM: Dra. en Psicología Paula Mastandrea · TT: Lic. Mariana Rodríguez</li>
<li><strong>Jefes Generales de Enseñanza Práctica</strong> — Prof. Ing. E. Fernández Galván · Prof. Ing. Jonathan Sánchez</li>
<li><strong>Jefe de Preceptores</strong> — TM: Lic. Raúl Biscione · TT: Lic. Erika Dibner</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.85rem"}},"textColor":"neutral"} -->
<p class="has-neutral-color has-text-color" style="font-size:0.85rem">TM: Turno Mañana · TT: Turno Tarde</p>
<!-- /wp:paragraph -->
HTML
);

// ---------------------------------------------------------------------
// Proyecto Institucional — placeholder (sin contenido real todavía en legacy)
// ---------------------------------------------------------------------
i13j_upsert_page(
	'proyecto-institucional',
	'Proyecto Institucional',
	<<<'HTML'
<!-- wp:paragraph -->
<p>Próximamente encontrará aquí información sobre el Proyecto Institucional.</p>
<!-- /wp:paragraph -->
HTML
);

// ---------------------------------------------------------------------
// Acceso a la 13 — reemplaza el flujo de SSO por una grilla simple de
// enlaces reales, tomados del legacy-site (post_name=acceso-a-la-13, ID 351).
// ---------------------------------------------------------------------
i13j_upsert_page(
	'acceso-a-la-13',
	'Acceso a la 13',
	<<<'HTML'
<!-- wp:paragraph -->
<p>Accesos rápidos a los sistemas y recursos que usa la comunidad del Instituto. Cada enlace abre el sitio del proveedor correspondiente en una pestaña nueva.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<div class="i13j-link-grid">
	<div class="i13j-link-card"><h3><a href="https://13dejulio.susyg.com/Login" target="_blank" rel="noopener noreferrer">Mis licencias →</a></h3></div>
	<div class="i13j-link-card"><h3><a href="https://mail.google.com" target="_blank" rel="noopener noreferrer">Gmail →</a></h3></div>
	<div class="i13j-link-card"><h3><a href="https://xhendra.ar/" target="_blank" rel="noopener noreferrer">Xhendra →</a></h3></div>
	<div class="i13j-link-card"><h3><a href="https://drive.alpha2000.com.ar" target="_blank" rel="noopener noreferrer">Recibos →</a></h3></div>
	<div class="i13j-link-card"><h3><a href="https://13dejulio.edu.ar/reservas" target="_blank" rel="noopener noreferrer">Reserva de salas →</a></h3></div>
	<div class="i13j-link-card"><h3><a href="https://drive.google.com/drive/u/0/folders/0AMe1Ah45jrB7Uk9PVA" target="_blank" rel="noopener noreferrer">Recursos para docentes →</a></h3></div>
</div>
<!-- /wp:html -->
HTML
);

// ---------------------------------------------------------------------
// Inscripciones — unifica los 3 flujos (Capital/Provincia/Sindicato) que
// antes eran páginas separadas (IDs 477, 471, 1765) en un único proceso
// con las diferencias marcadas por situación.
// ---------------------------------------------------------------------
i13j_upsert_page(
	'inscripciones',
	'Inscripciones',
	<<<'HTML'
<!-- wp:paragraph -->
<p>El proceso de inscripción tiene 3 pasos, iguales para todos los aspirantes. Elegí tu situación más abajo para ver los requisitos adicionales que le corresponden.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Paso 1 — Completar el formulario</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>El formulario a completar depende de tu situación (ver más abajo).</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Paso 2 — Documentación necesaria</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
<li>Una carpeta de cartulina verde, 3 solapas sin elástico y sin inscripciones.</li>
<li>Constancia de alumno regular, indicando plan de estudios a solicitar, en la escuela de origen.</li>
<li>Informe pedagógico, a completar por la escuela actual (<a href="https://13dejulio.edu.ar/wp-content/uploads/2022/05/InformePedagogico.pdf" target="_blank" rel="noopener noreferrer">descargar modelo</a>).</li>
<li>2 fotos 4×4.</li>
<li>Original y fotocopia de DNI o pasaporte del alumno/a (frente y dorso).</li>
<li>Original y fotocopia de la partida de nacimiento (no sirve el certificado de nacimiento).</li>
<li>Carnet de vacunas al día o libreta sanitaria.</li>
<li>Carnet de obra social, si está afiliado/a a una con número de emergencia médica.</li>
<li>Original y fotocopia de DNI o pasaporte de padres/madres o tutores (frente y dorso). Si la persona responsable no es el progenitor, incluir la documentación legal correspondiente.</li>
<li>Boletín de estudios del año anterior o del año en curso hasta la fecha.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>Paso 3 — Coordinar la entrevista</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Escribir a <a href="mailto:inscripciones@13dejulio.edu.ar">inscripciones@13dejulio.edu.ar</a> para solicitar una entrevista y entregar la documentación.</p>
<!-- /wp:paragraph -->

<!-- wp:separator -->
<hr class="wp-block-separator has-alpha-channel-opacity"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":2} -->
<h2>Requisitos según tu situación</h2>
<!-- /wp:heading -->

<!-- wp:html -->
<details class="i13j-inscripcion">
	<summary>Vengo de una escuela de Capital Federal (CABA)</summary>
	<p>Completá el <a href="https://forms.gle/KUL8tr9Bg2zUeSAQ7" target="_blank" rel="noopener noreferrer">formulario de inscripción</a> y seguí los pasos 2 y 3 de arriba. No hay requisitos adicionales.</p>
</details>

<details class="i13j-inscripcion">
	<summary>Vengo de una escuela de la Provincia de Buenos Aires</summary>
	<p><strong>Importante:</strong> si residís en provincia necesitás 7 años de escolaridad primaria para ingresar a primer año. Como el régimen de provincia contempla 6 años, tenés que cursar un año de secundaria en provincia antes de inscribirte en primer año, o bien cursar 7° grado en una escuela de CABA.</p>
	<p>Si cumplís con los 7 años de escolaridad, completá el mismo <a href="https://forms.gle/KUL8tr9Bg2zUeSAQ7" target="_blank" rel="noopener noreferrer">formulario de inscripción</a> y seguí los pasos 2 y 3 de arriba.</p>
</details>

<details class="i13j-inscripcion">
	<summary>Soy afiliado/a al Sindicato de Luz y Fuerza</summary>
	<p>Completá el <a href="https://forms.gle/1SAbF9r1QcZ6qJBMA" target="_blank" rel="noopener noreferrer">formulario de inscripción</a> y seguí los pasos 2 y 3 de arriba. Además, sumá a la documentación tu carnet de afiliado/a y el último recibo de sueldo.</p>
</details>
<!-- /wp:html -->
HTML
);

echo "\nMigración de contenido completa.\n";
