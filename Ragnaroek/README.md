1. Clone raki project
2. Change directory to Ratatoskr
3. Edit config-example.php and run:

    $ php transition-run.php ./config-example.php

4. Copy all schemas to /usr/share/midgard2 directory, so they can be used after migration.
5. Copy all configuration files from /etc/midgard/conf.d to /etc/midgard2/conf.d.
 * change [Database] section (to [Midgarddatabase])
