# Reputation System for VAM (Virtual Airlines Manager)
All files needed to update VAM (Virtual Airlines Manager) with the Reputation System

¿Y qué es eso de la reputacion?
Basicamente es una forma de ver la carrera profesional del piloto en nuestra compañia. Por ejemplo, un piloto que realice 
muchos vuelos al dia, con pocos errores, y que visite la web asiduamente tendrá más reputación que un piloto que no vuele 
apenas, o lo haga con errores, o que un piloto que no visite la web con asiduidad.

¿Cómo sube/baja mi reputación?
Hay varios factores que influyen en las variaciones de la reputación. Realizar vuelos con pocos errores incrementa la 
reputación, asi como realizarlos con algún error la incrementan, pero en menor medida. Completar Tours de la compañía, 
participar en eventos o hacer alguna aportación a la compañía (no económica) la incrementa de igual manera. Visitar la 
web para reservar vuelos o, simplemente, pasar a leer las novedades, la incrementa también. El echo de no tener actividad 
(como no realizar vuelos, no visitar la web, etc) disminuye la reputación según unos valores establecidos. Y así ocurre 
con toda la actividad que un piloto puede realizar en la compañía.

¿En qué me afecta como piloto mi reputación?
Actualmente, una reputación baja, impide al piloto beneficiarse de ciertos aspectos del sistema VAM. Alguno de ellos es la
capacidad de mover aeronaves de un aeropuerto hasta el que nos encontramos para así poder realizar un vuelo, o la capacidad 
de solicitar una ruta creada con SimBrief como Ruta Regular. El cambio mas interesante es la forma en que el piloto asciende/desciende de rango (se explicará mas abajo). En un futuro, se irán añadiendo mas variantes, todo ello será debidamente 
notificado.

¿Cómo afecta la reputación a mi rango?
Se han establecido unos valores mínimos y máximos para cada rango. Esto significa que para subir de rango necesitas alcanzar 
el valor mínimo de reputación exigido por el rango inmediatamente superior al que te encuentras. Es decir, si tienes como 
rango "Captain" y cumples con las horas máximas de éste rango, necesitas cumplir, además, con el valor mínimo de reputación 
del rango de "Senior Captain" para ascender a dicho rango. De esta forma, puede darse la situación de un piloto que tenga las 
horas necesarias para un rango superior al que se encuentra, pero que no ascienda por motivo de la reputación.
De igual forma sucede si la reputación baja, el piloto descenderá de rango independientemente de su número de horas de vuelo.

¿Qué sucede si mi reputación baja demasiado?
El sistema está pensado para que la reputación sólo baje del 10% en casos de inactividad continuada. Aún no se ha probado con 
efectividad, por lo que puede fallar. En caso de inactividad continuada, el sistema envía una alerta a los staff notificando 
de la inactividad del piloto para que se tomen medidas.


BUGFIXES:

- Now pilots cant view the block where the reputation is changed in their profiles. Only for staff with pilot_manager status.
