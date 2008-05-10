/*

LICENSE


This program is free software; you can redistribute it 
and/or modify it under the terms of the GNU General Public 
License (GPL) as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any 
later version.

This program is distributed in the hope that it will be 
useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A 
PARTICULAR PURPOSE.  See the GNU General Public License 
for more details.

To read the license please visit
http://www.gnu.org/copyleft/gpl.html

*/

#ifndef ADDRESSBOOK_H
#define ADDRESSBOOK_H

#include <QtSql>
#include <QString>

#define NUM_OF_TABLES 4

class AddressBook {

public:
	AddressBook() {

		QString uid_table_create = "CREATE TABLE uids (uid INTEGER PRIMARY KEY)";
		QString people_table_create = "CREATE TABLE people (person_id INT PRIMARY KEY, given_name CHAR(20), family_name CHAR(20), additional_name CHAR(20))";
		QString address_table_create = "CREATE TABLE addresses (address_id INT PRIMARY KEY, post_office_box TINYINT, extended_address VARCHAR(100), street_address VARCHAR(100), locality VARCHAR(40), region VARHAR(40), postal_code VARCHAR(10), country_name VARCHAR(40))";
		QString person2address_table_create = "CREATE TABLE person2address (person_id INT, address_id INT, relation CHAR(15))";

		db = QSqlDatabase::addDatabase("QSQLITE");//open database
		db.setDatabaseName(QDir::homePath() + "/.uab");
//		db.setDatabaseName(":memory:");
		if (!db.open()) {
			qFatal("SQL Driver not found");
			exit(1);
		}//end if ! db.open

		if(db.tables().size() < NUM_OF_TABLES) {

			QSqlQuery query;
			query.exec(uid_table_create);
			query.exec(address_table_create);
			query.exec(people_table_create);
			query.exec(person2address_table_create);

			int stevid = addPerson("Stephen", "Weber", "Paul");
			int louisaid = addAddress(0, "", "380 Louisa St.", "Kitchener", "Ontario", "N2H5N4", "Canada");
			person2address(stevid, louisaid, "home");

		}//end if not created

	}//end constructor

	int getUid() {
		QSqlQuery query;
		query.exec("INSERT INTO uids (uid) VALUES (null)");
		return query.lastInsertId().toInt();
	}

	int addPerson(char* given_name, char* family_name, char* additional_name) {
		QSqlQuery query;
		int uid = getUid();
		query.exec((new QString())->sprintf("INSERT INTO people (person_id, given_name, family_name, additional_name) VALUES (%i, '%s', '%s', '%s')", uid, given_name, family_name, additional_name));
		return uid;
	}

	int addAddress(int post_office_box, char* extended_address, char* street_address, char* locality, char* region, char* postal_code, char* country_name) {
		QSqlQuery query;
		int uid = getUid();
		query.exec((new QString())->sprintf("INSERT INTO addresses (address_id, post_office_box, extended_address, street_address, locality, region, postal_code, country_name) VALUES (%i, %i, '%s', '%s', '%s', '%s', '%s', '%s')", uid, post_office_box, extended_address, street_address, locality, region, postal_code, country_name));
		return uid;
	}

	void person2address(int person, int address, char* relation) {
			QSqlQuery query;
			query.exec((new QString())->sprintf("INSERT INTO person2address (person_id, address_id, relation) VALUES (%i, %i, '%s')", person, address, relation));
	}

	QSqlQueryModel* search(QString sql) {
		QSqlQueryModel* query = new QSqlQueryModel();
		query->setQuery(sql);
		if(query->lastError().isValid()) qDebug() << query->lastError();
		return query;
	}

protected:
	QSqlDatabase db;

};

#endif // ADDRESSBOOK_H
