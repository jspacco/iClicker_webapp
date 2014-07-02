README DOCUMENTATION
--------------------

In order to zip up a session, one must follow the steps below.

	1. Run the command "listcourses.py"
	    -This will give you a list of courses with the corresponding section_ids.
	     Note the section_id for the course you would like to zip up as you will need it later.

	2. Edit the "sessions-config.ini" file located in the C:\wamp\www\iClicker_webapp\scripts folder so that
	   you can zip any session whenever you need with one simple command. You will need a url, path, and desired section_id.
	   (More information provided in the file)


	3. Run the command "zipsessions.py nameOfCourse"
	   -If everything is configured correctly in the sessions-config.ini file, then this should zip up the session and put it in the specified path.

