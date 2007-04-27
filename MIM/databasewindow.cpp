#include "databasewindow.h"

#include <QMessageBox>

void DatabaseWindow::_init() {
   qDebug("DatabaseWindow::_init");
   lock(isLocked(),readOnlyBoxes);
   refresh();
}//end _init

void DatabaseWindow::closeEvent(QCloseEvent *event) {
   qDebug("DatabaseWindow::closeEvent");
   saveRecord();//save the current record, before closing
}//end closeEvent

/* RECORD NAVIGATION */

void DatabaseWindow::refresh(bool newRecord) {
   qDebug() << "DatabaseWindow::refresh" << newRecord;
   if(newRecord) {//if creating a new record
      qDebug("New Record");
      saveRecord();//save existing record
      unlock();
      currentRecord = getTable()->record(0);//get object copy of first record (so we preserve field names)
      currentRecord.clearValues();//clear out data
      currentRecordNumber = -2;//alert functions that we're on a new record
      setWindowTitle(getTable()->tableName() + " - New");
   } else {//otherwise, load the record
      qDebug("Load Record");
      currentRecord = getTable()->record(getCurrentRecordNumber());
      setWindowTitle(getTable()->tableName());
   }//end if-else newRecord

   //Put field data in appropriate controls
   QSqlField field;
   QObject *box;
   for(int i = 0; i < currentRecord.count(); i++) {//loop through record fields
      field = currentRecord.field(i);
      box = findChild<QLineEdit *>(field.name() + "Box");
      if(box != 0) {//if the field is a line edit
         ((QLineEdit *)box)->setText(field.value().toString());
      } else {//try another type
         box = findChild<QTextEdit *>(field.name() + "Box");
         if(box != 0) {
            ((QTextEdit *)box)->setPlainText(field.value().toString());
         } else {//try another type
		    box = findChild<QDateEdit *>(field.name() + "Box");
			if(box != 0) {
			   if(field.value().toString() != "")
			      ((QDateEdit *)box)->setDate(QDate::fromString(field.value().toString()));
			   else
			      ((QDateEdit *)box)->setDate(QDate(2000,1,1));
			} else {//try another type
			   box = findChild<QComboBox *>(field.name() + "Box");
			   if(box != 0) {
			      ((QComboBox *)box)->setCurrentIndex(((QComboBox *)box)->findText(field.value().toString()));
			   } else {
			      box = findChild<QCheckBox *>(field.name() + "Box");
				  if(box != 0) {
				     if(field.value().toString() == "1") ((QCheckBox *)box)->setCheckState(Qt::Checked);
				     if(field.value().toString() == "0") ((QCheckBox *)box)->setCheckState(Qt::Unchecked);
					 if(field.value().toString() == "2") ((QCheckBox *)box)->setCheckState(Qt::PartiallyChecked);
				  }//end if-elses checkBox
			   }//end if-elses comboBox != 0
			}//end if-elses dateEdit != 0
		 }//end if-elses textEdit != 0
      }//end if-elses lineEdit != 0
   }//end for i < currentRecord.count()
}//end refresh(bool)

void DatabaseWindow::refresh() {
   refresh(false);
}//end refresh

void DatabaseWindow::newRecord() {
   refresh(true);
}//end newRecord

void DatabaseWindow::saveRecord() {
   qDebug("DatabaseWindow::saveRecord");
   if(getCurrentRecordNumber() == -1){//if we have not gone to a record yet
      return;
   }//end if getCurrentRecordNumber() == -1

   //put control values in record
   QSqlField field;
   QObject *box;
   bool isNotBlank = false;//should we actually save, or is this record blank?
   bool isNotChanged = true;//should we actually save, or has nothing been changed?
   qDebug("[Initial] %s",isNotChanged ? "Not changed : true" : "Not changed : false");
   for(int i = 0; i < currentRecord.count(); i++) {//loop through record fields
      field = currentRecord.field(i);
      box = findChild<QLineEdit *>(field.name() + "Box");
      if(box != 0) {//if the field is a line edit
	     QString inputMask = "";
	     if(((QLineEdit *)box)->inputMask() != "") {
         inputMask = ((QLineEdit *)box)->inputMask();
			inputMask = inputMask.remove(QRegExp("[AaNnXx90Dd#HhBb<>!\\;]"));
			inputMask.chop(1);
		 }//end if inputMask
	       isNotBlank = isNotBlank || (((QLineEdit *)box)->text() != "" && ((QLineEdit *)box)->text() != "0" && ((QLineEdit *)box)->text() != inputMask);
	       isNotChanged = isNotChanged && ((((QLineEdit *)box)->text() == field.value().toString()) || (field.value().toString() == "" && (((QLineEdit *)box)->text() == inputMask) || (((QLineEdit *)box)->text() == "0")));
	       qDebug("[LineEdit (%s)] %s",field.name().toAscii().data(), isNotChanged ? "Not changed : true" : "Not changed : false");
          currentRecord.setValue(field.name(), ((QLineEdit *)box)->text());
      } else {//try another type
         box = findChild<QTextEdit *>(field.name() + "Box");
         if(box != 0) {
            isNotBlank = isNotBlank || (((QTextEdit *)box)->toPlainText() != "");
            isNotChanged = isNotChanged && (((QTextEdit *)box)->toPlainText() == field.value().toString());
            qDebug("[TextEdit (%s)] %s",field.name().toAscii().data(),isNotChanged ? "Not changed : true" : "Not changed : false");
            currentRecord.setValue(field.name(), ((QTextEdit *)box)->toPlainText());
         } else {//try another type
		    box = findChild<QDateEdit *>(field.name() + "Box");
			if(box != 0) {
			   isNotBlank = isNotBlank || (((QDateEdit *)box)->date() != QDate(2000,1,1));
			   isNotChanged = isNotChanged && ((((QDateEdit *)box)->date().toString() == field.value().toString()) || (field.value().toString() == "" && (((QDateEdit *)box)->date() == QDate(2000,1,1))));
			   qDebug("[DateEdit (%s)] %s",field.name().toAscii().data(),isNotChanged ? "Not changed : true" : "Not changed : false");
			   currentRecord.setValue(field.name(), ((QDateEdit *)box)->date().toString());
			} else {//try another type
			   box = findChild<QComboBox *>(field.name() + "Box");
			   if(box != 0) {
			      isNotBlank = isNotBlank || (((QComboBox *)box)->currentText() != "");
			      isNotChanged = isNotChanged && (((QComboBox *)box)->currentText() == field.value().toString());
			      qDebug("[ComboBox (%s)] %s",field.name().toAscii().data(),isNotChanged ? "Not changed : true" : "Not changed : false");
				   currentRecord.setValue(field.name(), ((QComboBox *)box)->currentText());
			   } else {
			      box = findChild<QCheckBox *>(field.name() + "Box");
				  if(box != 0) {
				     if(((QCheckBox *)box)->checkState() == Qt::Checked) {
				        isNotChanged = isNotChanged && (field.value().toString() == "1");
				        qDebug("[Checkbox (%s)] %s",field.name().toAscii().data(),isNotChanged ? "Not changed : true" : "Not changed : false");
				        currentRecord.setValue(field.name(), "1");
				     }//end if Qt::Checked
					  if(((QCheckBox *)box)->checkState() == Qt::Unchecked) {
				        isNotChanged = isNotChanged && (field.value().toString() == "0");
				        qDebug("[Checkbox (%s)] %s",field.name().toAscii().data(),isNotChanged ? "Not changed : true" : "Not changed : false");
					     currentRecord.setValue(field.name(), "0");
					  }//end if Qt::Unchecked
					  if(((QCheckBox *)box)->checkState() == Qt::PartiallyChecked) {
				        isNotChanged = isNotChanged && (field.value().toString() == "2");
				        qDebug("[Checkbox (%s)] %s",field.name().toAscii().data(),isNotChanged ? "Not changed : true" : "Not changed : false");
					     currentRecord.setValue(field.name(), "2");
					  }//end if Qt::PartiallyChecked
				  }//end if checkBox != 0
			   }//end if-elses comboBox != 0
			}//end if-elses dateEdit != 0
		 }//end if-elses textEdit != 0
      }//end if-elses lineEdit != 0
   }//end for i < currentRecord.count()

   if(!isNotBlank || isNotChanged)//if all fields are blank or nothing has changed
      return;
   
   if(getCurrentRecordNumber() != -2) {
      if(QMessageBox::question(this, "Save changes?", "Do you want to save your changes to this record?", QMessageBox::Yes | QMessageBox::No) != QMessageBox::Yes) return;
   }//end if != -2

   //comitt record to database
   if(getCurrentRecordNumber() == -2) {//if we are inserting a new record
      getTable()->insertRecord(-1, currentRecord);
   } else {//otherwise update record
      getTable()->setRecord(getCurrentRecordNumber(), currentRecord);
   }//end if-else getCurrentRecordNumber() == -2
   getTable()->submitAll();
   qDebug("Record saved");
}//end saveRecord

void DatabaseWindow::nextRecord() {
   gotoRecord(getCurrentRecordNumber() + 1);
}//end nextRecord

void DatabaseWindow::previousRecord() {
   gotoRecord(getCurrentRecordNumber() - 1);
}//end previousRecord

void DatabaseWindow::gotoRecord(int record) {
   qDebug("Goto record #%d",record);
   qDebug(isLocked() ? "Form is locked for editing" : "Form is unlocked for editing");
   if(!isLocked())//if we can't edit, why save?
      saveRecord();//save current record before going on
   currentRecordNumber = record;
   if(getCurrentRecordNumber() < 0) {
      currentRecordNumber = getTable()->rowCount() - 1;
   }//end if getCurrentRecordNumber() < 0
   if(getTable()->rowCount() <= getCurrentRecordNumber()) {
      currentRecordNumber = 0;
   }//end if getTable()->rowCount() <= getCurrentRecordNumber()
   refresh();
}//end gotoRecord

/* LOCKING */
bool DatabaseWindow::lock(bool state, QList<QObject *> *get) {
   qDebug() << "DatabaseWindow::lock" << state;
   _isLocked = state;
   QList<QLineEdit *> lineEdits = findChildren<QLineEdit *>(0);
   QList<QTextEdit *> textEdits = findChildren<QTextEdit *>(0);
   QList<QDateEdit *> dateEdits = findChildren<QDateEdit *>(0);
   QList<QComboBox *> comboBoxes = findChildren<QComboBox *>(0);
   QList<QCheckBox *> checkBoxes = findChildren<QCheckBox *>(0);

   for (int i = 0; i < lineEdits.size(); i++) {
      if(get == 0 || (get != 0 && !lineEdits.at(i)->isReadOnly())) {
         if(!readOnlyBoxes->contains(lineEdits.at(i)))
            lineEdits.at(i)->setReadOnly(state);
      } else {
         get->append(lineEdits.at(i));
      }//end if-else get == 0
   }//end for i < lineEdits.size()

   for (int i = 0; i < textEdits.size(); i++) {
      if(get == 0 || (get != 0 && !textEdits.at(i)->isReadOnly())) {
         if(!readOnlyBoxes->contains(textEdits.at(i)))
            textEdits.at(i)->setReadOnly(state);
      } else {
         get->append(textEdits.at(i));
      }//end if-else get == 0
   }//end for i < textEdits.size()
   
   for (int i = 0; i < dateEdits.size(); i++) {
      if(get == 0 || (get != 0 && !dateEdits.at(i)->isReadOnly())) {
         if(!readOnlyBoxes->contains(dateEdits.at(i)))
            dateEdits.at(i)->setReadOnly(state);
      } else {
         get->append(dateEdits.at(i));
      }//end if-else get == 0
   }//end for i < dateEdits.size()
   
   for (int i = 0; i < comboBoxes.size(); i++) {
      if(get == 0 || (get != 0 && !comboBoxes.at(i)->isEnabled())) {
         if(!readOnlyBoxes->contains(comboBoxes.at(i)))
            comboBoxes.at(i)->setEnabled(state);
      } else {
         get->append(comboBoxes.at(i));
      }//end if-else get == 0
   }//end for i < comboBoxes.size()
   
   for (int i = 0; i < checkBoxes.size(); i++) {
      if(get == 0 || (get != 0 && !checkBoxes.at(i)->isEnabled())) {
         if(!readOnlyBoxes->contains(checkBoxes.at(i)))
            checkBoxes.at(i)->setEnabled(state);
      } else {
         get->append(checkBoxes.at(i));
      }//end if-else get == 0
   }//end for i < comboBoxes.size()

   return isLocked();
}//end lock(bool state)

bool DatabaseWindow::lock(bool state) {
   return lock(state,0);
}//end lock(bool state)

void DatabaseWindow::lock() {
   lock(true);
}//end lock

void DatabaseWindow::unlock() {
   lock(false);
}//end unlock

bool DatabaseWindow::toggleLock() {
   return lock(!isLocked());
}//end toggleLock

/* DATA ACCESS */

int DatabaseWindow::getCurrentRecordNumber() {
   qDebug() << "DatabaseWindow::getCurrentRecordNumber ==" << currentRecordNumber;
   return currentRecordNumber;
}//end getCurrentRecordNumber()

bool DatabaseWindow::isLocked() {
   qDebug() << "DatabaseWindow::isLocked ==" << currentRecordNumber;
   return _isLocked;
}//end isLocked

QSqlTableModel* DatabaseWindow::getTable() {
   qDebug("DatabaseWindow::getTable");
   return _table;
}//end getTable
