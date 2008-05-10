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

#include <QString>
#include <QtSql>
#include "addressbook.h"

void printResults(QSqlQueryModel* query) {
	QSqlRecord loopRecord;
	int prev_id = 0;
	if(query->rowCount() == 0) printf("No results found\n");
	for(int i = 0; i < query->rowCount(); i++) {
		loopRecord = query->record(i);
		for(int j = 0; j < loopRecord.count(); j++) {
			if(strcmp(loopRecord.fieldName(j).toAscii().data(),"person_id") == 0
				|| strcmp(loopRecord.fieldName(j).toAscii().data(),"given_name") == 0
				|| strcmp(loopRecord.fieldName(j).toAscii().data(),"family_name") == 0
				|| strcmp(loopRecord.fieldName(j).toAscii().data(),"additional_name") == 0
				) {
					if(prev_id != loopRecord.value("person_id").toInt())
						printf("%s ", loopRecord.value(j).toString().toAscii().data());
			} else {
				if(strcmp(loopRecord.value(j).toString().toAscii().data(),"") != 0 && strcmp(loopRecord.value(j).toString().toAscii().data(),"0") != 0)
				printf("\n\t%s: %s", loopRecord.fieldName(j).toAscii().data(), loopRecord.value(j).toString().toAscii().data());
			}
		}//end for j
		prev_id = loopRecord.value("person_id").toInt();
		printf("\n");
	}//end for i
}// end printResults

int main( int argc, char **argv ) {

	AddressBook* addressBook = new AddressBook();
	if(argc > 2 && strcmp(argv[1],"add") == 0) {
		if(strcmp(argv[2],"person") == 0) {
			char* given_name = "";
			char* family_name = "";
			char* additional_name = "";
			for(int i = 0; i < argc-3; i++) {
				switch(i) {
					case 0:
						given_name = argv[i+3];
						break;
					case 1:
						family_name = argv[i+3];
						break;
					case 2:
						additional_name = argv[i+3];
						break;
				}//end switch
			}//end for i
			addressBook->addPerson(given_name, family_name, additional_name);
		} else if(strcmp(argv[2],"address") == 0) {
			int post_office_box = 0;
			char* extended_address = "";
			char* street_address = "";
			char* locality = "";
			char* region = "";
			char* postal_code = "";
			char* country_name = "";
			for(int i = 0; i < argc-3; i++) {
				switch(i) {
					case 0:
						post_office_box = atoi(argv[i+3]);
						break;
					case 1:
						extended_address = argv[i+3];
						break;
					case 2:
						street_address = argv[i+3];
						break;
					case 3:
						locality = argv[i+3];
						break;
					case 4:
						region = argv[i+3];
						break;
					case 5:
						postal_code = argv[i+3];
						break;
					case 6:
						country_name = argv[i+3];
						break;
				}//end switch
			}//end for i
			int addressid = addressBook->addAddress(post_office_box, extended_address, street_address, locality, region, postal_code, country_name);
			for(int i = 0; i < argc-(3+8); i++)
				addressBook->person2address(atoi(argv[8+3+i]), addressid, argv[9]);
		} else {
			printf("Item type '%s' not recognized\n", argv[2]);
			exit(2);
		}//end if-elses
	}//end if add
	if(argc > 2 && strcmp(argv[1],"associate") == 0) {
		if(argc > 5 && strcmp(argv[2],"address") == 0) {
			for(int i = 0; i < argc-5; i++)
				addressBook->person2address(atoi(argv[5+i]), atoi(argv[3]), argv[4]);
		} else {
			printf("Item type '%s' not recognized\n", argv[2]);
			exit(2);
		}//end if-elses
	}//end if associate
	if(argc > 2 && strcmp(argv[1],"search") == 0) {
		if(strcmp(argv[2],"person") == 0) {
			char* given_name = "";
			char* family_name = "";
			char* additional_name = "";
			for(int i = 0; i < argc-3; i++) {
				switch(i) {
					case 0:
						given_name = argv[i+3];
						break;
					case 1:
						family_name = argv[i+3];
						break;
					case 2:
						additional_name = argv[i+3];
						break;
				}//end switch
			}//end for i
			printResults(addressBook->search((new QString())->sprintf("SELECT * FROM people WHERE people.given_name LIKE '%%%s%%' AND people.family_name LIKE '%%%s%%' AND people.additional_name LIKE '%%%s%%'",given_name,family_name,additional_name).toAscii().data()));
			printf("\n");
			printResults(addressBook->search((new QString())->sprintf("SELECT people.*, addresses.* FROM people, addresses WHERE addresses.address_id IN (SELECT person2address.address_id FROM person2address WHERE person2address.person_id=people.person_id) AND people.given_name LIKE '%%%s%%' AND people.family_name LIKE '%%%s%%' AND people.additional_name LIKE '%%%s%%'",given_name,family_name,additional_name).toAscii().data()));
		}//end if person
	}//end if search

	return 0;

}// end main
