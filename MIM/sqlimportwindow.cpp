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

#include "sqlimportwindow.h"

void SqlImportWindow::done() {
	QStringList map;
	QComboBox *box;
	QSqlField field;
	for(int i = 0; i < from.count(); i++) {
		field = from.field(i);
		box = findChild<QComboBox *>(field.name() + "Box");
		map.append(box->currentText());
	}//end for from
	importFinish(map, mw, db);
	close();
}//end done()
