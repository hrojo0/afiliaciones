## About Project

This web application is focused in a simple form to register people that is going to be displayed on a map using the Maps JavaScript API to display the markers and find the location given in the form.

The register take in consideration the electoral limits in Tepic, Nayarit; México, where each registration can specify the electoral area to which it belongs.

The main functions of the app are:

- Register people basic information and their location using Maps JavaScript API.
- Display basic statistics of the amount of register and make filters to displays its data such as electoral area, municipality, age range, gender and more depending on the user level that is logged in the app.
- List the registers in two categories, affiliates and no affiliates where you can display and manage all its data.
- View the location of the registers on two differents kind of map: one with markers, separated by color if is affiliate or not, and other as a heatmap to view the influence of the registers

## Users section
To access the app you have to be registered in the database as a user, it has 7 levels of  user:
 - Admin, can access and manage all the aspects of the app such as control users and the registers
 - Super usuario, can do the same as the admin, this is intentional because the app is scalated this user will be modified to access only the information of one municipality.
 - Supervisor, can view and manage the information related to one municipality, such as registers and users asigned to their municipality. This level is the last one who can manage users.
 - Coordinador de demarcaciones, demarcaciones are the electoral areas so this user can view and manage the registers of these demarcaciones.
 - Responsable de demarcación, this users can only view and manage the registers of the demarcación assigned to them.
 - Responsable de zona, each demarcación can separate by zones to improve the distribution of people so this users only can access to the information of them.
 - Promotor, is the lowest level in the app, this user can only view and manage the registers assigned.

The web app is made with PHP, JQuery, AJAX, Maps JavaScript API, connection to a MySQL database where is defined trough a config.php file with basic information of the databse which is required in the conexion_db.php file.
