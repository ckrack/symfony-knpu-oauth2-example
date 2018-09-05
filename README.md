# Example: OAuth / Social Integration for Symfony: KnpUOAuth2ClientBundle

Example for using the custom provider / authenticator with KnpUOAuth2ClientBundle.

## Getting started

* Clone the Repo
* Configure the OAUTH settings in `.env` with your facebook connect credentials
* Create db schema with `php bin/console doctrine:schema:create` (this is only an example, so no migrations)
* `php bin/console server:start`
* Navigate to `/connect/facebook`
* The controller will print your facebook id after authenticating, and the user will be stored in the database
