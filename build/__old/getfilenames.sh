#!/bin/bash

FILES=`find $1 -type f -name "*.ini" -exec basename {} \;`

for d in $FILES
do
	echo "<filename>"$d"</filename>"
done
