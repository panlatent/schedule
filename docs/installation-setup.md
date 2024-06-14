# Installation and Setup



You can install Schedule via the plugin store, or through Composer.

## Craft Plugin Store

To install Schedule, navigate to the Plugin Store section of your Craft control panel, search for Schedule, and click the Install/Try button.

## Composer

1. Open your terminal and go to your Craft project:

    ```bash
      cd /path/to/project
    ```

2. Then tell Composer to load the plugin:

=== "DDEV"

    ``` bash
    ddev composer require panlatent/schedule
    ```

=== "Host"

    ``` bash
    composer require panlatent/schedule
    ```

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Schedule.

## Setup 


4. Add a record to system crontab:

        * * * * * php /path/to/craft schedules/run 1>> /dev/null 2>&1

   Or use built-in `schedules/listen` command:

   ```shell
   $ ./craft schedules/listen
   ```

   If you use DDEV:

   ```shell
   $ ddev craft schedules/listen
   ```