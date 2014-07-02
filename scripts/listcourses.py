#!/c/Python27/python.exe

import urllib2

def main():
 
        listcourses_url='http://localhost:8888/iClicker_webapp/iclicker/listcourses.php'
        print wget(listcourses_url)
        return

def wget(url):
        response = urllib2.urlopen(url)
        headers = response.info()
        data = response.read()
        return data

if __name__=='__main__':
        main()
