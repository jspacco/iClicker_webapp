#!/bin/bash

#
# Only installs PHP
#

#SERVER='jspacco@cs.knox.edu:webpage/iclicker'
SERVER='jspacco@cs.knox.edu:webpage/jspacco/cs141'

cd iclicker

LIST=''
FILES=`ls *php`
for f in $FILES; do
    if [ "$f" != "dbconn.php" ]
    then
	#echo $f
	LIST="$LIST $f"
    fi
done

#echo $LIST
scp -r $LIST css $SERVER

#
# now do the same for css files
#
# cd css

# LIST=''
# FILES=`ls *css`
# for f in $FILES; do
#     if [ "$f" != "dbconn.php" ]
#     then
# 	#echo $f
# 	LIST="$LIST $f"
#     fi
# done

#echo $LIST
scp $LIST $SERVER/css


#cd ..
