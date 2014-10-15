#!/bin/bash
# Dump the database
mysqldump --add-drop-table -u admin -p`cat /etc/psa/.psa.shadow` db27949_ski > backup/db27949_ski.sql

