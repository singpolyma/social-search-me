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

#ifndef SQLIMPORTWINDOW_H
#define SQLIMPORTWINDOW_H

#include <QtCore>
#include <QtGui>
#include <QtSql>
#include <QMessageBox>
#include <QtDebug>

class SqlImportWindow : public QWidget
{
  Q_OBJECT

public:
   SqlImportWindow(QSqlRecord from, QSqlRecord to) : QWidget() {


     //from and to are records so that we can extract field names for mapping -- we don't do any actual importing here, just map the fields across
     
     QComboBox *tmp;
     for(int i = 0; i < from.count(); i++) {
        tmp = new QComboBox(this);
//        tmp->setObjectName(from.fieldName(i) + "Box");
        tmp->setToolTip(from.fieldName(i));
        tmp->setGeometry(QRect(300, 10 + (20*i), 100, 20));
        tmp->setEditable(false);
        tmp->setInsertPolicy(QComboBox::NoInsert);
        for(int x = 0; x < to.count(); x++) {
           tmp->addItem(to.fieldName(x));
        }//end for x < to.count()
//        addWidget(tmp);
     }//end for i < from.count()

   }//end constructor

};

#endif // SQLIMPORTWINDOW_H
