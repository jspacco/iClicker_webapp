README DOCUMENTATION
--------------------

In order to zip up a session, one must follow the steps below.

	1. Run the command "listcourses.py"
	    -This will give you a list of courses with the corresponding section_ids.
	     Note the section_id for the course you would like to zip up as you will need it later.

	2. Edit the "sessions-config.ini" file located in the current folder so that
	   you can zip any session whenever you need with one simple command. You will need a url, path, and desired section_id.
	   (More information provided in the file).
	   	 section_id is whatever the "listcourses.py" script from step #1 tells you
		 url is the url of the PITS installation, which will look something like this: http://cs.knox.edu/pits
		 path is the path to the folder containing the class from the USB stick you use with your clicker base station. For example, your USB stick contains a folder with your iclicker executable named something like "iClicker Win 6.2.4". This folder will contain a folder named "Classes", which in turn contains a folder for your class, named something like "CS-101-1". You want the full or relative path to this folder to be set as the "path" variable in the sessions-config.ini file.
	   
	4. Run the command "zipsessions.py nameOfCourse"
	   -If everything is configured correctly in the sessions-config.ini file, then this should zip up the session and put it in the specified path.

