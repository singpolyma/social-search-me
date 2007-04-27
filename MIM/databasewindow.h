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
   virtual void gotoRecord(int record);
   virtual void saveRecord();

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
