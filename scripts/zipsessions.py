#!/c/Python27/python.exe

import sys
import os
import shutil
import zipfile
import tempfile
import glob
import time

def main():
        date=time.strftime("%Y-%m-%d-%H:%M")

	# support Python 2 and Python 3
        if sys.version_info[0]==2:
            import ConfigParser
            config = ConfigParser.ConfigParser()
        elif sys.version_info[0]==3:
            import configparser
            config = configparser.ConfigParser()

        config.read("sessions-config.ini")

        if len(sys.argv) > 1:
                selectedCourse=sys.argv[1]
        elif config.has_option('CurrentCourse', 'selected_course'):
                selectedCourse=config.get('CurrentCourse', 'selected_course')
        else:
                print('You must specify a course name either on the command line, or as the selected_course attribute of CurrentCourse')
                sys.exit(1)

        if not config.has_section(selectedCourse):
                print('No course config information for %s in sessions-config.ini' % (selectedCourse, selectedCourse))
                sys.exit(1)

        URL = config.get(selectedCourse,'url')
        directory = config.get(selectedCourse,'path')
        zipname = "Sessions-Section-" + config.get(selectedCourse,'section_id') + "-" + date + ".zip"
        print("Zipping session for the course: %s" % (selectedCourse))


        # Get the list of sessions that have been uploaded to the
        # course with the chosen section_id

        sessionstr=(URL + "?section_id=%s" % zipname)
        sessions={}
        for s in sessionstr.split():
                sessions[s]=1
        #print sessions

        # Get the missing sessions
        missing=getMissingSessions(directory, sessions)
        print(missing)

        zipcsvs(directory, missing, zipname)


def getMissingSessions(dir, sess):
        missing=[]
        for f in os.listdir(os.path.join(dir + "/SessionData")):
                if f.startswith("L") and f.endswith(".csv"):
                        if not f.replace('.csv', '') in sess:
                                missing.append(f)
        return missing

def wget(url):
        # Support Python 2 or Python 3
        if sys.version_info[0]==2:
                import urllib2
                response = urllib2.urlopen(url)
                headers = response.info()
                data = response.read()
                return data
        elif sys.version_info[0]==3:
                from urllib.request import urlopen
                response = urlopen(url)
                headers = response.info()
                data = response.read()
                return data

def zipcsvs(dir, missing, zipname):
        '''
        Create an overall tempdir
        Create tempdirs for each of the sessions named dataL..........
        Copy matching csv and image files into the appropriate session dirs
        Zip the overall tempdir
        '''

        if len(missing)==0:
                print("No outstanding sessions!  Nothing to zip and upload")
                return

        print('Will create zip for upload of the following sessions:')
        for s in missing:
                print('\t',s)

        # Create overall tempdir
        tmpdir=tempfile.mkdtemp()

        print('Created temporary directory: %s' % tmpdir)

        for csv in missing:
                sesname=csv.replace('.csv', '')
                subdir=os.path.join(tmpdir, 'sessions', 'data'+sesname)
                os.makedirs(subdir)

                print('Created data directory for session %s' % sesname)
                
                # Copy the .csv file
                shutil.copy(os.path.join(dir, "SessionData", csv), subdir)
                # Find the image files
                # glob is awesome, btw

                print('Copying image files for session %s' % sesname)

                for f in glob.glob(os.path.join(dir, 'Images', '%s*.jpg' % sesname)):
                        # Copy the image files
                        shutil.copy(f, subdir)

                print('Done copying image files for session %s' % sesname)
        
        # zip the whole tmpdir into a zipfile
        print('Zipping sessions into %s' % zipname)

        # shutil's make_archive only exists in 2.7 and above
        #shutil.make_archive(zipname, "zip", tmpdir)
        zipdir(tmpdir, zipname)

        print('Removing temporary directory %s' % tmpdir)

        shutil.rmtree(tmpdir)


def zipdir(path, outfile):
        #print "PATH",path
        # function for zipping a directory from:
        # http://stackoverflow.com/questions/1855095/how-to-create-a-zip-archive-of-a-directory-in-python
        zip=zipfile.ZipFile(outfile, "w")
        for root, dirs, files in os.walk(path):
                for file in files:
                        # Name of the file relative to the archive
                        # We don't want the whole path to the tempdir
                        '''
                        arcname=os.path.join(root, file).replace(path+'/', '')
                        zip.write(os.path.join(root, file), arcname)
                        '''
                        zip.write(os.path.join(root, file))
        zip.close()

if __name__=='__main__':
        main()


