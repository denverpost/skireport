#!/bin/bash
# Dump the database
mysqldump --add-drop-table -u admin -p$DB_PASS db27949_ski > ../backup/db27949_ski.sql

