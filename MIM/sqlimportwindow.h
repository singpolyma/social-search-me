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
	SqlImportWindow(QSqlRecord from, QSqlRecord to, void(* importFinish)(QStringList,void*,QSqlDatabase), void* mw, QSqlDatabase db) : QWidget() {

	  //from and to are records so that we can extract field names for mapping -- we don't do any actual importing here, just map the fields across
	  
		this->from = from;
		this->importFinish = *importFinish;
		this->mw = mw;
		this->db = db;
	  
		QComboBox *combo_tmp;
		QLabel *label_tmp;
		label_tmp = new QLabel(this);
		label_tmp->setGeometry(QRect(10, 10, 100, 20));
		label_tmp->setText("Imported Fields");
		label_tmp = new QLabel(this);
		label_tmp->setGeometry(QRect(200, 10, 100, 20));
		label_tmp->setText("MIM Fields");
		for(int i = 0; i < from.count(); i++) {
			label_tmp = new QLabel(this);
			label_tmp->setGeometry(QRect(10, 40 + (23*i), 100, 20));
			label_tmp->setText(from.fieldName(i));
			combo_tmp = new QComboBox(this);
			combo_tmp->setObjectName(from.fieldName(i) + "Box");
			combo_tmp->setToolTip(from.fieldName(i));
			combo_tmp->setGeometry(QRect(200, 40 + (23*i), 100, 20));
			combo_tmp->setEditable(false);
			combo_tmp->setInsertPolicy(QComboBox::NoInsert);
			combo_tmp->addItem("[none]");
			for(int x = 0; x < to.count(); x++) {
				combo_tmp->addItem(to.fieldName(x));
			}//end for x < to.count()
		}//end for i < from.count()

		QPushButton *doneButton = new QPushButton(this);
		doneButton->setGeometry(QRect(200, 50 + (23*from.count()), 100, 20));
		doneButton->setText(QApplication::translate("Import", "Done", 0, QApplication::UnicodeUTF8));
		connect(doneButton, SIGNAL(clicked()), this, SLOT(done()));

	}//end constructor

public slots:
	virtual void done();

protected:
	QSqlRecord from;
	void (*importFinish)(QStringList, void*, QSqlDatabase);
	void* mw;
	QSqlDatabase db;

};

#endif // SQLIMPORTWINDOW_H
