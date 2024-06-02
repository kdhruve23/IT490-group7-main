#!/bin/bash

echo "Hello what directory would you like to scan?"
echo "Please make sure to include the appropriate '/'."

read dir

echo "scanning $dir for malware, please hold.."
clamscan --recursive $dir

echo "ALL DONE!!"


