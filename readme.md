
# Getting Started

# Installation

## 1. Base Install

Installs whmcs_plex to the WHMCS system so that it can be found by WHMCS


1. Clone the whmcs_plex repo.

   ```
   sudo git clone https://github.com/zenjabba/whmcs_plex /var/www/html/modules/servers

## 2. Getting Your Plex Token
Navigate to any media item in your library. Using the `Get Info` option and now select View XML. At the end of that URL you will see `X-Plex-Token=` save that value in the module settings and record it for next few steps. 

[Detailed steps here](https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token/)

## 3. Getting Your Plex Machine ID
You will browse the users that you are friends with using the following URL

https://plex.tv/api/users?X-Plex-Token={PLEXTOKENGOESHERE}

Look for an existing user on the server you want to use with this module. Under each user is a `<Server>` tag in this tag you will find the `serverId ` and the `machineIdentifier` save both of these in the module configuration settings. You will need this `machineIdentifier` for the sections JSON in the next step.

## 4. Setting Up Your Section JSON

We now need the ids for your sections. Browse the following URL:

https://plex.tv/api/servers/{MACHINEIDGOESHERE}?X-Plex-Client-Identifier=whmcs_plex&X-Plex-Token={PLEXTOKENGOESHERE}

Here you will see all of your sections for the server you have selected. Inside the `<Server>` tag there is a collection of `<Section>` tags. You will need the `title` attribute and the `id` attribute. The JSON you are building will look like this:

`{
  "Title":id,
  "Title":id
 }`
 
until you have all sections in your library mapped. Paste this JSON into the module settings. You will also grab the `id` of the library that you intend to use to let users know that they are suspended and put this into the module settings as well.


Please Note: Create a folder in "modules/servers" and clone the repo into it. Edit the whmcs_plex.php file, change the "whmcs_plex" function to the name of your folder. Also, rename the whmcs_plex.php file to match the folder name.




