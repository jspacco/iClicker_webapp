#!/usr/bin/env python

import os
import sys
import shutil
import zipfile
import urllib2
import tempfile
import glob

#
# Global variable for the URL of the server to list courses
#
listcourses_url='http://localhost:8890/iclicker/listcourses.php'

#
# Global variable for finding out new sessions
#
checksessions_url='http://localhost:8890/iclicker/checksessions.php'

def usage():
	print '''%s --listcourses <url>
List all courses and their corresponding section_id.  Most courses will only have a single section, so in practice the section_id is how we identify the course

or

%s <section_id> <directory> [ <url> ]
Create a zipfile of new sessions contained in <directory>, to be uploaded into the section and course with the given section_id.  Basically, this script needs the section_id to ask the server located at <url> which sessions have already been uploaded to a particular course.
''' % (sys.argv[0], sys.argv[0])
	sys.exit()

def main():
	global listcourses_url
	global checksessions_url
	if len(sys.argv) < 2:
		usage()

	if sys.argv[1]=='--listcourses' or sys.argv[1]=='-l':
		if len(sys.argv) > 2:
			listcourses_url=sys.argv[2]
		print wget(listcourses_url)
		return
	if len(sys.argv) < 3:
		usage()
	section_id=sys.argv[1]
	directory=sys.argv[2]
	if len(sys.argv)>3:
		checksessions_url=sys.argv[3]

	# Get the list of sessions that have been uploaded to the
	# course with the chosen section_id
	sessionstr=wget(checksessions_url + "?section_id=%s" % section_id)
	sessions={}
	for s in sessionstr.split():
		sessions[s]=1
	#print sessions

	# Get the missing essions
	missing=getMissingSessions(directory, sessions)
	#print missing

	zipcsvs(directory, missing)


def getMissingSessions(dir, sess):
	missing=[]
	for f in os.listdir(os.path.join(dir, "SessionData")):
		if f.startswith("L") and f.endswith(".csv"):
			if not f.replace('.csv', '') in sess:
				missing.append(f)
	return missing

def wget(url):
	response = urllib2.urlopen(url)
	headers = response.info()
	data = response.read()
	return data

def zipcsvs(dir, missing):
	'''
	Create an overall tempdir
	Create tempdirs for each of the sessions named dataL..........
	Copy matching csv and image files into the appropriate session dirs
	Zip the overall tempdir
	'''

	print 'Will create zip for upload of the following sessions:'
	for s in missing:
		print '\t',s

	# Create overall tempdir
	tmpdir=tempfile.mkdtemp()

	print 'Created temporary directory: %s' % tmpdir

	for csv in missing:
		sesname=csv.replace('.csv', '')
		subdir=os.path.join(tmpdir, 'sessions', 'data'+sesname)
		os.makedirs(subdir)

		print 'Created data directory for session %s' % sesname
		
		# Copy the .csv file
		shutil.copy(os.path.join(dir, "SessionData", csv), subdir)
		# Find the image files
		# glob is awesome, btw

		print 'Copying image files for session %s' % sesname

		for f in glob.glob(os.path.join(dir, 'Images', '%s*.jpg' % sesname)):
			# Copy the image files
			shutil.copy(f, subdir)

		print 'Done copying image files for session %s' % sesname
			
	
	# zip the whole tmpdir into a zipfile
	zipname='data.zip'
	print 'Zipping sessions into %s' % zipname

	shutil.make_archive(zipname, "zip", tmpdir)
	#zipdir(tmpdir, zipname)

	print 'Removing temporary directory %s' % tmpdir

	shutil.rmtree(tmpdir)


def zipdir(path, outfile):
	# function for zipping a directory from:
	# http://stackoverflow.com/questions/1855095/how-to-create-a-zip-archive-of-a-directory-in-python
	zip=zipfile.ZipFile(outfile, "w")
	for root, dirs, files in os.walk(path):
		for file in files:
			zip.write(os.path.join(root, file))
	zip.close()


def zipcsv(csvpath):
	csvname = os.path.basename(csvpath)
	tempdirpath = os.path.dirname(csvpath)				# move to SessionData directory
	basepath = os.path.dirname(tempdirpath)				# move up to base directory
	tempdirname = "data" + csvname.split(os.extsep)[0]
	tempdirpath = os.path.join(basepath, tempdirname)	# append temporary directory name

	# look for the file
	print "Looking for file: " + csvpath

	if not os.path.isfile(csvpath):
		print "Couldn't find: " + csvpath + "."
		sys.exit(1)

	# create a temporary directory, deleting any directory which would interfere
	# holy crap, this sounds dangerous...
	if (os.path.exists(tempdirpath)):
		print "Directory already exists. Removing..."
		shutil.rmtree(tempdirpath)
	print "Creating temporary directory: " + tempdirpath
	os.makedirs(tempdirpath)
	
	# copy pictures into the temporary directory
	for file in os.listdir(os.path.join(basepath, "Images")):
		if tempdirname[4:] == file[0:len(tempdirname[4:])]:
			# copy this file
			print "Copying file " + file + " to " + tempdirpath
			shutil.copy(os.path.join(basepath, "Images/" + file), tempdirpath)
	
	# copy the csv into the temporary directory
	print "Copying file " + csvname + " to " + tempdirpath
	shutil.copy(csvpath, tempdirpath)
	
	# zip the temporary directory
	print "Zipping " + tempdirpath + " to " + tempdirname + ".zip"
	zip = zipfile.ZipFile(tempdirname + ".zip", "w")
	zipdir(tempdirpath, zip)
	zip.close()
	
	# remove the temporary directory
	print "Removing temporary directory " + tempdirpath
	shutil.rmtree(tempdirpath)
	
if __name__=='__main__':
	main()
