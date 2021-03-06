[![Logo](resources/img/logo_plain.png)][uptime-robot-site]


# Uptime Robot plugin for Craft CMS 3.x

Monitor your Craft CMS sites with [Uptime Robot][uptime-robot-site] from the control panel.


## Requirements

This plugin requires Craft CMS 3.0.0 or later.


## Installation


### The easy way

Just install the plugin from the [Craft Plugin Store][craft-plugin-store].


### Using Composer

  - Install with Composer from your project directory: `composer require la-haute-societe/craft-uptime-robot`
  - In the Craft Control Panel, go to Settings → Plugins and click the **Install** button for Uptime Robot plugin.


## Uptime Robot Overview

Uptime Robot is a website monitoring service which offers up to 50 free monitors.

This plugin can help you to supervise the availabilty of your Craft site by adding related Uptime Robot monitors.

You can add a monitor for any entry and site you wish.

You can also select which user will receive email alerts if the site goes down.

Without even leaving your Craft site, access your monitors uptime informations and detailed logs of events.


## Configuring Uptime Robot


### Uptime Robot account
To use this plugin, you will have to open an Uptime Monitor account first.

You can do so by visiting their [signup page](https://uptimerobot.com/signUp).


### Setup the API Key

Once logged into your Uptime Robot account, you will be able to get your API key in the "My Settings" menu.

In the lower right section of the page, you can click the "Show/hide it" link to reveal the API key, generate and copy it.

![Get an Uptime Robot API key](resources/img/uptime-robot-api-key.png)

Copy the API key within the Uptime Robot plugin settings and validate to check that it is able to talk to the Uptime Robot services.

![Settings screen](resources/img/uptime-robot-settings.png)
> **Note:**
> Once you will start adding monitors, it's not recommended to update the API key with a different account, otherwise your existing monitors informations will become unavailable from the Craft admin panel.


## Using Uptime Robot


### Add a monitor

You can add a monitor from the main administration panel by clicking the "Add new monitor" button in the upper right corner of the screen.

> **Note:**
> If your Uptime Robot account runs out of available monitors, the button won't show up.

On the following screen, select an entry you wish to monitor. 

Optionaly, select the user(s) which will receive the monitoring alerts.

Click the "Star monitor" button in the upper right corner of the screen to save the monitor and start the monitoring.

You're done!

![Add a monitor screen](resources/img/uptime-robot-add-monitor.png)
> **Notes:**
> 
> * When adding a user for the first time, a corresponding account will be created on the Uptime Robot service. The user will then receive an email to activate his account in order to receive the notifications.
> * If your Craft environement support multiple sites, you can select the desired one in the according drop down. 
> * There will be a slight delay between the monitor creation and its effective start during which it will appear as "Not checked yet". That is a normal behavior.


### View monitors

Every monitored entry will show up in the main Uptime Robot administration panel.
From there, you will get the monitors status and uptime informations.

To edit a monitor configuration, click the according pen icon.

![View monitors screen](resources/img/uptime-robot-view-monitors.png)
> **Notes:**
> 
> * From that screen, you will see how many remaining monitors are available from your Uptime Robot account.
> * The monitors informations are refreshed every 5 minutes.


### Detail view of a monitor

For each monitor, you can get more detailed informations, response times and the list of related events.

#### Details
![Detail monitor view screen](resources/img/uptime-robot-view-monitors-details.png)

#### Response times
![Response time view screen](resources/img/uptime-robot-view-monitors-response-time.png)

#### Events
![Events view screen](resources/img/uptime-robot-view-monitors-events.png)


### Edit a monitor

The edition screen allows you to change the entry and the alert contacts users.

You can also remove the monitor by using the "Delete" action from the upper right menu button.

![Edit monitor screen](resources/img/uptime-robot-edit-monitor.png)
> **Warning:**
> Be aware that if you remove a monitor, you will lose every monitoring logs from the Uptime Robot service accordingly.


### Uptime Robot users permissions

From the users permissions panel, you will be able to set the following permissions:

* Add monitors
* View monitors
* Edit monitors
* Remove monitors

If the Craft environement support multiple sites, you will be able to set the permissions per site.

![User permission screen](resources/img/uptime-robot-user-permissions.png)
> **Note:**
> User will only be able to add monitors for the sites he owns the permission. 


### Uptime Robot Widget

You can use the Uptime Robot widget to list monitors with there status and uptime informations. Simply add the widget from the dashboard screen by clicking the "New widget" button and select the "Uptime Robot" widget.


## Licensing

This plugin is free to try in development environments, but requires payment to be used in production environments. The license fee for this plugin is $29 (license + one year of updates) via the Craft Plugin Store, then $5 per year for updates (optional).


## Uptime Robot Roadmap

* Be able to set various parameters like interval and HTTP Authentication per monitor
* Handle Keyword monitor type with dedicated Twig macro to inject the keyword in the entry page
* Be able to pause/restart a monitor
* More useful widget
* More user friendly interface (Custom element type to handle monitors management)

Brought to you by [![LHS Logo](resources/img/lhs.png) La Haute Société][lhs-site].

Uptime Robot is a trademark of Uptime Robot Service Provider Ltd.

[uptime-robot-site]: https://uptimerobot.com
[lhs-site]: https://www.lahautesociete.com
[craft-plugin-store]: https://plugins.craftcms.com
