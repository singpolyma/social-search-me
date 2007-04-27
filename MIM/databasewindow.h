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

#ifndef DATABASEWINDOW_H
#define DATABASEWINDOW_H

#include <QtCore/QVariant>
#include <QtGui/QApplication>
#include <QtGui/QLineEdit>
#include <QtGui/QMainWindow>
#include <QtGui/QTextEdit>
#include <QtGui/QDateEdit>
#include <QtGui/QComboBox>
#include <QtGui/QWidget>
#include <QtGui/QCheckBox>
#include <QRegExp>
#include <QDesktopWidget>
#include <QtSql>
#include <QMessageBox>
#include <QtDebug>

class DatabaseWindow : public QMainWindow
{
  Q_OBJECT

public:
   DatabaseWindow(int record, bool locked, QSqlTableModel *table, bool init) : QMainWindow() {

     //Use these lines in subclass to set up Qt Designer window
     //setupUi(this);
     //_init();

     _table = table;

     currentRecordNumber = -1;//alerts functions that we are not set up yet

     

     readOnlyBoxes = new QList<QObject *>();
     lock(locked,readOnlyBoxes);
     gotoRecord(record);

     if(init)
        _init();

   }//end constructor

   //data access
   virtual int getCurrentRecordNumber();
   virtual bool isLocked();
   virtual QSqlTableModel* getTable();

public slots:
   //record navigation
   virtual void refresh(bool newRecord);
   virtual void refresh();
   virtual void newRecord();
   virtual void nextRecord();
   virtual void previousRecord();
   virtual void firstRecord();
   virtual void lastRecord();
   virtual void gotoRecord(int record);
   virtual void saveRecord();
   virtual void deleteRecord();

   //locking   
   virtual bool lock(bool state, QList<QObject *> *get);
   virtual bool lock(bool state);
   virtual void lock();
   virtual void unlock();
   virtual bool toggleLock();

protected:
   int currentRecordNumber;
   bool _isLocked;
   QSqlTableModel *_table;
   QSqlRecord currentRecord;
   QList<QObject *> *readOnlyBoxes;
   virtual void closeEvent(QCloseEvent *event);
   virtual void _init();

};

#endif // DATABASEWINDOW_H
