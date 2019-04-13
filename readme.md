# DAW II. Docker & Docker-Compose

Documento de apoyo a la 2ª clase de Docker en el que empezamos la parte práctica. A lo largo de este manual, entiendo que ya sabes que es una imagen de docker y entiendes qué significa contendor. Así que, sabiendo eso, podemos arrancar el primer contenedor.

# Docker
Como se menciona en la documentación oficial, la herramienta **docker** se encarga de gestionar un solo contenedor, pudiendo crearlo, arrancarlo, pararlo o acceder a él. Es útil cuando tu intención es realizar una acción sobre un solo contenedor.

## run
El primer paso es crear un contenedor, que en nuestro caso será un servidor web Nginx. Para ello, ejecutamos la siguiente sentencia:
```bash
docker run -dit -p 8000:80 --name k1_nginx -v $(pwd):/app/ --workdir /app nginx:1.14.2-alpine sh
```
Analicemos punto por punto la anterior instrucción:
* run crea y arranca el contenedor.
* -dit le indica a Docker que arranque el contenedor en modo _deattach_, es decir, en segundo plano. Así mismo, -it indica que queremos que sea interactivo y nos asigne una consola para poder trabajar en él. Esto hará que el contenedor no se cierre cuando termine de realizar el comando especificado en el último parámetro.
* -p 8000:80 Indica que queremos abrir el puerto 8000 de nuestra máquina real y lo vincule con el puerto 80 de nuestro contenedor de Docker. Así, cuando accedamos a nuestra máquina por el puerto 8000, Docker redirigirá la petición al puerto 80 de nuestro contenedor. El formato es puertoReal:puertoContainer, notándose los dos puntos usados de separador.
* --name k1_nginx Simplemente indica el nombre que tendrá el contenedor. Nos será más fácil referirnos al contenedor a través de este nombre que a través del ID.
* -v $(pwd):/app/ Monta un volumen (una especie de carpeta compartida entre nuestra máquina y el contenedor Docker). La terminal sustituirá $(pwd) por nuestro directorio actual (póngase /home/happyuser/project como ejemplo). Así, todos los ficheros que estén contenidos en /home/happyuser/project serán compartidos con la carpeta /app dentro del Docker y las modificaciones que efectuemos en ellos desde un lado (máquina real o contenedor) serán realizadas también en el otro. Piense en esto como un enlace simbólico.
* --workdir /app Es nuestro directorio de trabajo por defecto y Docker nos saltará a ese directorio cuando entremos en el contenedor. Así nos evitamos tener que acceder con cd a este directorio. Piense en esto como el directorio por defecto.
* nginx:1.14.2-alpine Es la imagen que queremos usar para crear nuestro contenedor. Todo contenedor de Docker necesita una imagen a partir de la cual crearse. En este caso estamos usando la imagen nginx y una versión de esta imagen llamada 1.14.2-alpine. Nótese la separación con los dos puntos entre nombre de la imagen : versión de la imagen.
* Por último ```sh``` es el comando que vamos a pedir que ejecute cuando el contenedor sea arrancado. Generalmente, aquí insertaríamos un comando que ejecute el programa servidor que necesitamos, como ```nginx```, pero para depurar pondremos solamente la consola.

## exec
Genial! Ya tenemos un contenedor Docker levantado y arrancado. De momento no hace nada, ya que hemos indicado SH como comando inicial y eso solo abre la consola. En este punto podemos "conectarnos" al contenedor de docker a través de la consola y ejecutar comandos y sentencias dentro del propio contenedor:

```docker exec -it {nombre_del_contenedor} {comando a ejecutar}```
Este comando nos permite ejecutar acciones dentro del contenedor. Teniendo en cuenta que nuestro contenedor se llama k1_nginx, podemos ejecutar una consola para poder acceder al docker como si fuese una terminal de nuestro propio ordenador utilizando el comando a ejecutar como ```sh```. O si queremos arrancar el servidor web sin acceder a la consola, podemos lanzar como comando a ejecturar:
```docker exec -it k1_nginx nginx```

Si lanzas nginx en el contenedor y accedes desde tu navegador la dirección ```http://localhost:8000``` deberías estar viendo la más que bonita página por defecto de Nginx. Recuerda que entramos a través del puerto 8000 de nuestra máquina porque decidimos que este puerto se redireccionará con el puerto 80 dentro del contenedor.

## start
Ahora que ya hemos creado nuestro contenedor decides que es hora de hacer un descanso y apagas tu ordenador. Cuando vuelves intentas levantar el contenedor de docker con el comando run pero te sale un error por pantalla. Esto se debe a que **run** ya ha creado el contenedor y no puede volverlo a hacer con el mismo nombre.
Si nuestro contenedor ya está creado, tenemos que arrancarlo con start:

```docker start {nombre_del_contenedor}```
```docker start k1_nginx```

## stop y remove
¿Quieres parar tu contenedor? Para eso existe el comando stop de docker: detiene el contenedor al que hagas referencia. Si ahora intentas entrar en ```http://localhost:8000``` veras un mensaje de error. Claro! el contenedor que estaba preparado para mostrarte la página de Nginx está apagado.
```docker stop {nombre_del_contenedor}```

¿Te has equivocado al crear el container en algún parámetro? ¿Te has cansado dl proyecto? En definitiva, ¿Quieres eliminar el contenedor? Eso lo puedes hacer con rm
```docker rm {nombre_del_contenedor}```
Esto borrará el contenedor como si nunca hubiese existido. Podrás volver a ejecutar la sentencia de **docker run** que escribimos al principio, porque el contenedor con ese nombre ya no exisitirá.

## Resumen del comando docker
Bien, hemos sido capaces de levantar un contenedor, pararlo, eliminarlo y conectarnos a él a través de una consola de comandos. Si cambiamos el nombre que recibe el contenedor en el comando run podríamos tener dos contenedores independientes levantados. Esto esta genial para hacer pruebas de montaje y para proyectos pequeños.

Sin embargo, un proyecto de mayor magnitud generalmente necesitará varios contenedores y posiblemente varias instancias (copias idénticas) de cada contenedor activas: cuatro contenedores de Nginx, cuatro contenedores de PHP, una par de contenedores de Redis, etc... Gestionar esta situación con el comando docker puede volverse un poco tedioso. Para ello, te vendría mejor usar docker-compose.

# docker-compose
Docker te permite gestionar de forma inteligente todos los contenedores que tu proyecto necesita gracias a la herramienta docker-compose, que realizará procedimientos en todos los contenedores que hayas definido en tu aplicación. Quieres arrancar todos los contenedores a la vez: docker-compose up (o start). Quieres pararlos: docker-compose stop. Una sentencia para todo. ¿Más cómodo que ejecutar diez veces un comando, no?

Junto a este documento encontrarás un proyecto super básico de Docker que utiliza dos servicios: un servidor web Nginx y un servidor de PHP. ¿Servicio? ¿Qué es eso de servicio? Ahora lo vemos.

Mientras lees este documento, es buena idea que abras en otra pestaña o ventana el fichero ```docker-compose.yml``` que encontrarás en la raíz del proyecto. Verás que tiene tres entradas principales: ```version, networks``` y ```services```.

```version``` indica en que formato está definido el fichero. Existen tres versiones, pero te aconsejo que te olvides un poco de la 1 y de la 2, ya que están quedando obsoletas. Aprende la versión 3, la actual, que es la que está mejor soportada por docker y entorno (como Kubernetes).

```networks``` indica las redes internas de docker que vamos a crear. Una network, o red en español, permite contectar contenedores de docker como si una red local fuera. De esta forma, podemos conectar entre sí dos o más contenedores para que puedan acceder entre ellos y dejar fuera otros. Suponte que tienes tres servicios: A, B, C. Queremos que A pueda "hablar" con B pero no con C. También queremos que B pueda hablar con C. Para ello uniremos A con B y B con C mediante dos redes. Lo verás más claro un poco después.

Por último, ```services``` define los servicios que vamos a utilizar. ¿Por qué se les llama servicios? Bien, por lo general, un servicio es un contenedor de docker. En nuestro proyecto queremos utilizar tres contenedores diferentes por lo que crearemos tres servicios: nginx, php y un tercer contenedor de pruebas. Importante: tres servicios que se materializan en tres contenedores de docker completamente independientes. Sin embargo, si la aplicación crece en tráfico cuando trabajemos en la nube, podemos crear dos o más contenedores exactamente iguales de un mismo servicio. Así tendremos, por ejemplo, 4 contenedores del servicio nginx, 4 contenedoresdel servicio php y un par de contenedores del tercer servicio. Por ello se llama servicio y no contenedores, porque de un servicio puedes obtener más de un contenedor.

En cualquier caso, vamos a definir tres servicios: 
* nginx como servidor web. Se encargará de recibir las peticiones de nuestro navegador.
* php como servidor de dinamismo. Se encargará de generar las páginas HTML de forma dinámica.
* other, que es simplemente un contenedor que no hace nada, pero aprovecho para indicarte otra forma de crear un contenedor desde el ```docker-compose.yml```.

Dentro de cada servicio del ```docker-compose.yml``` se le está indicando a docker cómo tiene que crear los contenedores, que puertos tiene que abrir a nuestra máquina, que volúmenes están disponibles y a qué red (o network) pertenece. Con eso podemos crear las configuraciones del contenedor, pero no le estamos indicando la imagen de la cual tiene que generar los containers directamente. Eso lo vamos a hacer desde un Dockerfile. 

El Dockerfile es un fichero que le indica a Docker qué tiene que hacer en cada contenedor. Generalmente, tendrás que realizar modificaciones de la imagen que nos hemos bajado para que sea funcional en nuestro proyecto: crear ficheros de configuración, instalar paquetería, etc. Mientras que en el ```docker-compose.yml``` generalmente indicamos qué cualidades y conexiones tiene el contenedor, en el ```Dockerfile``` indicamos comandos que tendrán que ser ejecutados dentro del propio contenedor. En este caso, los Dockerfile son muy sencillos: FROM indica la imagen padre de nuestro contenedor, CMD indica el comando que se debe ejecutar cuando se arranque el contenedor (como hacíamos en el último parámetro de docker run .... ) y por último EXPOSE abre los puertos de comunicación a otros contenedores de Docker (pero no a nuestra máquina física). En el Dockerfile también podríamos haber indicado que se tienen que instalar algunos paquetes con ```apt-get```, copiar algún fichero de configuración con ```ADD```, etc...

Volvamos a ```docker-compose.yml```. Fíjate que el tercer servicio no tiene un build context definido. Dado que es un contenedor de pruebas al que no hay que realizar ninguna modificación, no merece la pena crear un Dockerfile; con que se baje la imagen nos vale. No hay nada más que hacer.

¡Genial! Ya tienes una base sobre qué ficheros necesita docker-compose. Vamos a crear nuestros contenedores. Vete a una consola y sitúate en la raíz del proyecto, donde esté el docker-compose.yml. Vamos a crear y arrancar nuestros contenedores con:
```docker-compose build && docker-compose start```
¡Y ya está! ¡Están vivos! Solo un comando para levantar tres contenedores.

Cuando estés en fase de desarrollo, te será un poco más útil el comando ```docker-compose up``` que te va mostrando en la consola los mensajes que cada contenedor docker emite. Tienes en una ventana una visión global de todos los dockers que están corriendo.

También es útil que sepas que puedes indicar sobre qué servicios deseas realizar la operación: ```docker-compose start nginx``` solo arrancará el contenedor de nginx y dejará los otros sin tocar.

Cuando quieras tomarte una pausa, puedes parar los contenedores con ```docker-compose stop``` y también puedes parar y eliminar todos los contenedores del proyecto con ```docker-compose down``` que no dejará rastro de los contenedores.

En fin, espero que esta sencilla documentación te sirva para aclararte un poco más del nivel más básico de Docker: cómo empezar a crear contenedores. Sin embargo, aún te queda mucho mundo que explorar acerca de Docker. Te recomiendo que cuando entiendas todo este documento sigas explorando a través de los canales oficiales, Stackoverflow y demás páginas de Internet, aunque la mejor forma de aprender es que te aparezcan problemas reales y tengas que buscar cómo resolverlos.

En cualquier caso, no te desanimes, ánimo y a por ello !