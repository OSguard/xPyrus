{*

xPyrus - Framework for Community and knowledge exchange
Copyright (C) 2003-2008 UniHelp e.V., Germany

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, only version 3 of the
License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see http://www.gnu.org/licenses/agpl.txt

*}Hallo {$newUser->getUsername()} -- Willkommen bei [[local.local.project_domain]],


Um Deinen Account freizuschalten gehe bitte auf folgenden Link:

{user_management_url activate=$actString extern="1"}


Danach kannst Du Dich mit dem von Dir gewählten Passwort und Usernamen
einloggen:
        Dein Username: {$newUser->getUsername()}
        Passwort: (bei der Anmeldung festgelegt)

Du kannst selbst entscheiden, wie viele und welche persönliche
Informationen Du welchen Usern anzeigen lässt. Dies kannst du
unter „Einstellungen“ ändern.

Nach der Freischaltung Deines Accounts genießt Du den vollen
Funktionsumfang von [[local.local.project_domain]]:
    - Tausche fachliche Informationen in den Foren zu Deinen Fächern
    - Diskutiere mit anderen Usern über Gott und die Welt in vielen 
      weiteren Foren
    - Besorg Dir wichtige Unterlagen zu Deinem Studium und stelle
      anderen Usern Deine Unterlagen zur Verfügung
    - Knüpfe Kontakte zu anderen Studenten in {$local_city->getName()}

Alles andere findest Du selbst heraus auf [[local.local.project_domain]]!

Allen neuen Usern empfehlen wir, die meistgestellten Fragen (FAQ)
als erste Hilfe zu nutzen:
    {help_url extern="1" faq="1"}
Benutze dazu auch die Forensuche:
    {forum_url search="1" extern="1"}

Bei Problemen mit der Anmeldung kannst Du Dich unter 
{mantis_url extern="1"} melden.
Versuch bitte, den Fehler möglichst genau zu beschreiben!

Viel Spaß auf [[local.local.project_domain]]!

Dein [[local.local.project_name]]-Team
