Hey Rich,

I'm sorry I couldn't get all of this done by the deadline. Things have been hectic and even with the extension (which I very much appreciate), I couldn't really sit down to this until around 8 last night and coded straight through since then. I did not complete all of the objectives, a lot of functionality is missing. I knew I didn't have time to set up a framework so I just started coding and saw what I could get through. The data model ended up being pretty aggressive and the path I had set upon, I felt I either had to see this ETL stuff through or end up with nothing to show at all.

Anyway, if you want to check out what did get done, it's pretty easy to set up. Credentials for a MySQL DB are in application/db/db.php just change those to some db. Data model is set up by running

source /path/to/db/ddl/db.ddl in the MySQL monitor

After that there's only one thing to do which is run

php cli/ETL.php

from the main directory.. it literally has zero output cuz I have to go to work but it should correctly populate that entire data model.

I'm happy to talk if you have any questions or want to follow up.

Thanks again for your time, let me know if you have any issues trying to run it but I was testing a lot
