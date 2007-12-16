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
PARTICULAR PURPOSE.	See the GNU General Public License 
for more details.

To read the license please visit
http://www.gnu.org/copyleft/gpl.html

*/

#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QApplication>
#include <QMainWindow>
#include <QtSql>
#include <QDesktopWidget>
#include <QStringList>
#include <QShortcut>
#include <QKeySequence>
#include <QTableView>
#include <QVariant>
#include <QMap>
#include <QErrorMessage>
#include <QFileDialog>

#include "ui_mainwindow.h"
#include "addresswindowimpl.h"
#include "mailinglabelsimpl.h"
#include "sxwplaindriver.h"
#include "sqlimportwindow.h"

#define NUM_OF_TABLES 3

class MainWindowImpl : public QMainWindow, Ui::MainWindow
{
	Q_OBJECT

public:
	QSqlDatabase db;//database link

	MainWindowImpl() : QMainWindow() {

		//WARNING - CURRENCY SYMBOL IS USED AS PRIMARY KEY IN PROGRAM (IE, SYMBOL IS STORED IN ADDRESSES TABLE)
		QString databaseTables[NUM_OF_TABLES] = {"Addresses","Currencies","Addresses2Categories"};
		QString databaseStructure[NUM_OF_TABLES] = {"CREATE TABLE IF NOT EXISTS Addresses (id INTEGER PRIMARY KEY, isOrganization INTEGER, title VARCHAR(5), firstName VARCHAR(20), lastName VARCHAR(20), organization VARCHAR(20), streetAddress VARCHAR(50), city VARCHAR(20), province VARCHAR(20), postalCode VARCHAR(10), country VARCHAR(20), spouseName VARCHAR(20), homePhone VARCHAR(11), workPhone VARCHAR(11), workExtension VARCHAR(3), fax VARCHAR(11), cellPhone VARCHAR(11), email VARCHAR(50), url VARCHAR(255), birthdate DATE, anniversary DATE, notes TEXT, support INTEGER, currency VARCHAR(3), period INTEGER)",\
"CREATE TABLE IF NOT EXISTS Currencies (id INTEGER PRIMARY KEY, symbol CHAR(3), value INTEGER)",\
"CREATE TABLE IF NOT EXISTS Addresses2Categories (id INTEGER PRIMARY KEY, category VARCHAR(20), address_id INTEGER)"\
};
		QString databaseInitialData[NUM_OF_TABLES] = {"INSERT INTO Addresses values(null, 1, '', '', '', 'MIM Project', '', '', '', '', '', '', '', '', '', '', '', 'mim@singpolyma.net', 'http://mim.singpolyma.net/', '', '', '', 0, 'CDN', 0)",\
"INSERT INTO Currencies values(null, 'CDN', 1)\nINSERT INTO Currencies values(null, 'USD', 0.97)",\
""\
};

		QStringList args = qApp->arguments();
		if(args.contains("--help") || args.contains("\?") || args.contains("/?") || args.contains("-h")) {
			printf("\nMissionary Information Manager - pre-release 1.1197759106.\nQt version %s\n\nSupported arguments:\n\
      --help                Displays this information.\n\
      --debug               Displays more obvious debug data.\n\
      --import <filename>   Starts an import.\n\
      --schema              Outputs the SQL for the initial database structure.\n\n",qVersion());
			exit(0);
		}//end if --help
		if(args.contains("--debug")) QErrorMessage::qtHandler();
		if(args.contains("--schema")) {
			for(int i = 0; i < NUM_OF_TABLES; i++) {
				printf("%s\n",databaseStructure[i].toAscii().data());
				printf("%s\n",databaseInitialData[i].toAscii().data());
			}//end for NUM_OF_TABLES
			exit(0);
		}//end if --schema

		setupUi(this);

		//window style/position
		setWindowFlags(Qt::CustomizeWindowHint);
		setWindowFlags(Qt::WindowTitleHint);
		setWindowFlags(Qt::WindowSystemMenuHint);
		setWindowFlags(Qt::WindowMinimizeButtonHint);
		move(qApp->desktop()->screenGeometry(this).center().x() - (width()/2),qApp->desktop()->screenGeometry(this).center().y() - height());

		db = QSqlDatabase::addDatabase("QSQLITE");//open database
		db.setDatabaseName(QDir::homePath() + "/.mimdb");
		if (!db.open()) {
			 qFatal("SQL Driver not found");
			 qApp->exit(1);
		}//end if ! db.open

		
		/* DATABASE SETUP */
		QStringList tables = db.tables();
		if(tables.size() < NUM_OF_TABLES) {
			QSqlQuery query;
			for(int i = 0; i < NUM_OF_TABLES; i++) {
				if(!tables.contains(databaseTables[i])) {
					query.exec(databaseStructure[i]);
					QStringList tmp = databaseInitialData[i].split("\n");
					for(int j = 0; j < tmp.size(); j++)
						query.exec(tmp.at(j));
				}//end if ! contains
			}//for numOfTables
		}//end if tables < numOfTables

		addressTable = new QSqlTableModel();
		addressTable->setTable("Addresses");
		addressTable->setEditStrategy(QSqlTableModel::OnManualSubmit);
		addressTable->setSort(3,Qt::AscendingOrder);
		addressTable->setSort(4,Qt::AscendingOrder);
		addressTable->setSort(5,Qt::AscendingOrder);
		addressTable->setSort(1,Qt::AscendingOrder);
		addressTable->select();
		
		currencyTable = new QSqlTableModel();
		currencyTable->setTable("Currencies");
		currencyTable->setEditStrategy(QSqlTableModel::OnManualSubmit);
		currencyTable->select();
		
		enterEvent(new QEvent(QEvent::None));

		connect(viewAddressesButton, SIGNAL(clicked()), this, SLOT(viewAddressesWindow()));
		connect(addAddressButton, SIGNAL(clicked()), this, SLOT(addAddresses()));
		connect(actionAbout_Qt, SIGNAL(activated()), qApp, SLOT(aboutQt()));
		connect(actionAbout_MIM, SIGNAL(activated()), this, SLOT(about()));
		connect(actionImport, SIGNAL(activated()), this, SLOT(import()));
		connect(nameSelectBox, SIGNAL(currentIndexChanged(int)), this, SLOT(selectBoxActivated(int)));
		connect(mailingLabelsButton, SIGNAL(clicked()), this, SLOT(showMailingLabels()));
		new QShortcut(*new QKeySequence("F11"), this, SLOT(f11()), SLOT(f11()), Qt::ApplicationShortcut);

	}//end constructor

	virtual void closeEvent(QCloseEvent *event);
	virtual QSqlTableModel* getAddressTable() {return addressTable;};

protected slots:
	virtual AddressWindowImpl* viewAddressesWindow();
	virtual AddressWindowImpl* viewAddressesWindow(int record,bool locked);
	virtual void addAddresses();
	virtual void selectBoxActivated(int record);
	virtual void f11();
	virtual void showMailingLabels();
	virtual void about();
	virtual void import();
	virtual void import(QString fileName);
	
protected:
	QSqlTableModel *addressTable;
	QSqlTableModel *currencyTable;
	virtual void enterEvent(QEvent *event);
	virtual void showEvent(QEvent *event);

};
#endif // MAINWINDOW_H
