<?php
/**
 * UptimeRobot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

/**
 * UptimeRobot en Translation
 *
 * Returns an array with the string to be translated (as passed to `Craft::t('uptime-robot', '...')`) as
 * the key, and the translation as the value.
 *
 * http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 */

return [
    'UptimeRobot plugin loaded'                                                                                       => 'Plugin UptimeRobot chargé',
    'Entry to monitor'                                                                                                => 'Entrée à surveiller',
    'Contacts to receive monitor alerts'                                                                              => 'Destinataires des alertes de la sonde',
    'Entry'                                                                                                           => 'Entrée',
    'Users'                                                                                                           => 'Utilisateurs',
    'Main API Key'                                                                                                    => 'Clé d\'API principale',
    'You will find your main API key here.'                                                                           => 'Vous trouverez votre clé d\'API principale ici.',
    'No monitors found'                                                                                               => 'Aucunes sondes trouvées',
    'Add monitor'                                                                                                     => 'Ajouter une sonde',
    'Edit monitor'                                                                                                    => 'Modifier la sonde',
    'Click the \'Add new monitor\' button to create one.'                                                             => 'Cliquez sur \'Ajouter une sonde\' pour en créer une.',
    'Add new monitor'                                                                                                 => 'Ajouter une sonde',
    'Start monitor'                                                                                                   => 'Démarrer la sonde',
    'Name'                                                                                                            => 'Nom',
    'Status'                                                                                                          => 'Etat',
    'Type'                                                                                                            => 'Type',
    'Uptime'                                                                                                          => 'Disponibilité',
    'Unknown'                                                                                                         => 'Inconnu',
    'Please, select an entry to monitor'                                                                              => 'Veuillez sélectionner une entrée à surveiller',
    'That entry is already monitored'                                                                                 => 'Cette entrée est déjà surveillée',
    'Monitor has been successfully added.'                                                                            => 'La sonde a été ajoutée avec succès.',
    'Monitor has been successfully updated.'                                                                          => 'La sonde a été mise à jour avec succès.',
    'An error occurred while deleting the monitor.'                                                                   => 'Une erreur est survenue pendant la suppression de la sonde.',
    'Monitor has been successfully deleted.'                                                                          => 'La sonde a été supprimée avec succès.',
    'Edit monitor "{name}"'                                                                                           => 'Modification de la sonde "{name}"',
    'View monitors'                                                                                                   => 'Afficher les sondes',
    'Edit monitors'                                                                                                   => 'Modifier les sondes',
    'Remove monitors'                                                                                                 => 'Supprimer les sondes',
    '{monitorsLeft,plural,=0{No remaining monitor available} =1{One remaining monitor} other{# remaining monitors}}.' => '{monitorsLeft,plural,=0{Il ne reste aucune sonde disponible} =1{Une sonde restante} other{# sondes restantes}}.',
    'That type of monitor cannot be modified by the Uptime Robot plugin .'                                            => 'Ce type de moniteur ne peut pas être modifié par le plug-in Uptime Robot.',
    'Confirm delete'                                                                                                  => 'Confirmation de suppression',
    'Are you sure you want to delete this monitor?'                                                                   => 'Êtes-vous sûr de vouloir supprimer cette sonde ?',
    'Are you sure you want to delete "{name}" monitor?'                                                               => 'Êtes-vous sûr de vouloir supprimer la sonde "{name}" ?',
    'The related Uptime Robot monitor seems to not exists anymore.'                                                   => 'La sonde Uptime Robot associée ne semble plus exister.',
    'OK'                                                                                                              => 'OK',
    'Cancel'                                                                                                          => 'Annuler',
    'Update monitor'                                                          => 'Mettre à jour la sonde',
    'Events'                                                                  => 'Evénements',
    'Paused'                                                                  => 'En pause',
    'Started'                                                                 => 'Démarré',
    'Duration'                                                                => 'Durée',
    'Reason'                                                                  => 'Raison',
    'Details'                                                                 => 'Informations',
    'Every {interval,plural,=1{# minute} other{# minutes}}'                   => 'Toutes {interval,plural,=1{les minutes} other{les # minutes}}',
    'View monitor "{name}"'                                                   => 'Visualisation de la sonde "{name}"',
    'An error occured while trying to connect to Uptime Robot API: {message}' => 'Une erreur est survenue pendant une tentative de connexion à l\'API d\'Uptime Robot : {message}',
    'Average response time'                                                   => 'Temps de réponse moyen',
    '{response_time} milliseconds'                                            => '{response_time} millisecondes'
];

