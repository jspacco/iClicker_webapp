#!/usr/bin/env python

import os
import sys
import shutil
import zipfile
import argparse
import urllib2

# extract into function, then have it take a class folder and create data for each session in the class maybe

# function for zipping a directory from:
# http://stackoverflow.com/questions/1855095/how-to-create-a-zip-archive-of-a-directory-in-python
def zipdir(path, zip):
	for root, dirs, files in os.walk(path):
		for file in files:
			zip.write(os.path.join(root, file))

def main():
	# parser = argparse.ArgumentParser(description='Zip up sessions to be uploaded to the JETS server. '+
	# 				 'Uploads must be made to a particular section_id ' +
	# 				 '(courses may have one or more sections, ' +
	# 	'although in practice most courses will have only one section). ' +
	# 	"\n\n" +
	# 	'For example: "%s 3 CS101" would zip up all new sessions from the CS101 folder ' % sys.argv[0] +
	# 	'(which will be in CS101/SessionData) that have not already been uploaded to section_id 3.')
	# parser.add_argument('section_id', metavar='<section_id>', type=str, action='store',
	# 		    help='the section_id where you want to upload the sessions. ' +
	# 	'Note that for courses with only one section, this is basically the courseId.')
	# parser.add_argument('course_dir', metavar='<directory>', type=str, action='store',
	# 		    help='The path to the directory for the course.')
	# parser.add_argument('--listcourses', action='store_true', required=False,
	# 		    help='List all of the courses and their possible section_ids')
	# args = parser.parse_args()

	# print args.listcourses

	if len(sys.argv) < 2:
		usage()

	if sys.argv[1]=='--listcourses' or sys.argv[1]=='-l':
		print listcourses()
		return
	if len(sys.argv) < 3:
		usage()
	section_id=sys.argv[1]
	directory=sys.argv[2]
	if len(sys.argv)>3:
		url=sys.argv[3]

	# Find the dir
	# Ask about the sessions
	# 

	zipcsv(sys.argv[1])


def listcourses(url):
	response = urllib2.urlopen(url)
	headers = response.info()
	data = response.read()
	return data

def usage():
	print '''%s --listcourses
List all courses and their corresponding section_id.  Most courses will only have a single section, so in practice the section_id is how we identify the course

or

%s <section_id> <directory> [ <url> ]
Create a zipfile of new sessions contained in <directory>, to be uploaded into the section and course with the given section_id.  Basically, this script needs the section_id to ask the server located at <url> which sessions have already been uploaded to a particular course.
''' % (sys.argv[0], sys.argv[0])
	sys.exit()

def zipcsv(csvpath):
	'''
	TODO:
	Query for the already uploaded session names
	Keep 
	'''
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
