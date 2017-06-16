# RazorSQL-Bridge-Adapted
An adaptation of the bridge used by the Razor database client to the new versions of PHP.

--------

La mayoría de alojamientos web no permiten que sus usuarios accedan a sus bases de datos de forma remota.

En cambio, ofrecen soluciones como el uso de un gestor web (como PhpMyAdmin) o en el mejor de los casos, proveen herramientas engorrosas como el uso de túneles SSH.

La solución a este problema es utilizar un fichero PHP alojado como una página web que permita al cliente comunicarse con la base de datos. Por supuesto, el cliente debe ser capaz de utilizar estos puentes, como RazorSQL.

Algunos clientes como RazorSQL proveen esta solución. Nos ofrecen un fichero PHP que al alojarlo en nuestro servidor, y después de configurar el cliente con la ruta a este fichero, permite al programa de escritorio comunicarse con la base de datos usando el fichero PHP como puente entre la base de datos MySQL y la aplicación de escritorio.

La razón por la que he creado este repositorio es que ese fichero PHP que nos provee RazorSQL se ha quedado anticuado. Las nuevas versiones de PHP han modificado ligeramente la sintaxis utilizada para el uso de MySQL. Por ejemplo, las funciones no se llaman "mysql", ahora se llaman "mysqli", y en la mayoría de casos se ha apostado por una sintaxis orientada a objetos en vez de utilizar los métodos procedimentales (funciones) que conocíamos hasta ahora.

Bajo este repositorio podéis encontrar los ficheros PHP que hacen de puente, modificado para adaptarse a las nuevas versiones PHP.

Estos ficheros son de especial interés para aquellos que usáis estos puentes y os habéis encontrado que ahora no funcionan, porque PHP7 ya no soporta los métodos antiguos.

Podéis consultar la información y los ficheros originales (anticuados) en [el siguiente enlace](http://razorsql.com/docs/razorsql_mysql_bridge.html).
