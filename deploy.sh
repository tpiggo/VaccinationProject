#!/bin/bash

cd VaccinationProject

git pull

cd frontend
echo "IN" 
echo $PWD
 
npm install
npm run build

echo "finished installing npm"
echo $PWD

cd ../..

echo "moving to build the top level directory"
echo $PWD

cp -r VaccinationProject/frontend/build/* ./
chmod 755 static
chmod 755 static/css
chmod 755 static/js

echo $PWD

mkdir backend
cp VaccinationProject/backend/* backend
echo "be sure to remember to replace the contents within the DBConnection for hte connection on mysql"

chmod 755 backend

echo "DOne" 
