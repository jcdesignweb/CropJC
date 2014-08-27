CropJC
=========

CropJC es una plugin para realizar crop de imagenes.



Instalación
--------------

- Ingrese su el archivo jquery.cropjc.js dentro de su carpeta donde tiene los los .js
- Debe crear un archivo php para luego utilizarlo
- Debe copiar la clase php llamada cropjc.php 
- Crear una carpeta para colocar las imagenes y darle permisos de escritura


### Ejemplo de uso:

```sh
<link rel="stylesheet" href="css/jquery-ui.css"> <!-- Es importante que pongan este archivo -->

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script> <!-- Cargar jQuery -->
<script type="text/javascript" src="js/jquery.cropjc.js"></script>


```

```sh
<script type="text/javascript">

$(function() {
	
	var options = {
		url: "ajax.php", // Nombre de archivo .php que creamos.
		display_crop: true, 
		cropped_path: "http://mipagina.com/resources/" // Indicamos la url completa que apunta la carpeta
	};
	
	
	$("body").CropJC(options); // Aca llamamos al plugin pasandole el objeto
	
});

</script>

<body>


</body>

```
#### En su archivo php - ajax.php

```sh
include_once("cropjc.php"); // incluimos nuestra clase

// seteamos estos valores que son requeridos ambos
$default = array(
	"destination_path" => "resources", // Carpeta
	"quality" => 98 // Calidad de la imagen (0 - 100)
);

$cropjc = new cropjc($default);

echo $cropjc->Crop();

```

Version
----

1.0

Licencia
-----------

CropJC es de uso libre, 

License
----

MIT



 Contacto: juan14nob@gmail.com 


