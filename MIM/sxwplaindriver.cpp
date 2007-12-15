/*
 * Copyright (C) 2007 Simon A. Wilper
 *
 * This file is part of the Qt SQL Plaintext Database Driver.
 * 
 * The Qt SQL Plaintext Database Driver is free software;
 * you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * The Qt SQL Plaintext Database Driver is distributed
 * in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this application; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

#include "sxwplaindriver.h"

SxWPlainDriver::SxWPlainDriver() {}
SxWPlainDriver::~SxWPlainDriver() {}

bool SxWPlainDriver::hasFeature( DriverFeature feature ) const {
    switch ( feature ) {
        case QSqlDriver::BLOB:
        case QSqlDriver::Transactions:
        case QSqlDriver::PositionalPlaceholders:
            return false;
        case QSqlDriver::Unicode:
            return true;
        default:
            return false;
    }
}

bool SxWPlainDriver::open(
        const QString& db,
        const QString& user,
        const QString& password,
        const QString& host,
        int port,
        const QString& options
        ) {
    f = new QFile( db );

    printf( "Opening File: %s\n", db.toAscii().data() );
    if ( !f->open( QIODevice::ReadWrite | QIODevice::Text ) ) {
        return false;
    }
    
    setOpen( true );
    setOpenError( false );

    return true;
}

void SxWPlainDriver::close() {
    if ( isOpen() ) {
        f->close();

        setOpen( false );
        setOpenError( false );
    }
}

QSqlResult* SxWPlainDriver::createResult() const {
    return new SxWResult( this );
}

/** SxWResult Implementation
 * Constructor
 * @param const QSqlDriver* driver
 */
SxWResult::SxWResult( const QSqlDriver* driver ) : QSqlResult( driver )  {
    // get file from driver and read all lines into memory
    // what happens to be a stringlist
    f = ((SxWPlainDriver*)driver)->getFile();
    while ( !f->atEnd() ) {
        // append to stringlist
        contents << QString( f->readLine() ).trimmed();
    }
    // rewind to beginning of file
    f->seek( 0 );
    
    fieldNames = row(0);
    contents.removeAt(0);
    
}

SxWResult::~SxWResult() {}

QStringList SxWResult::row( int row ) {
    //crazy char-by-char is in case some are "blah","blah" and some are blah,blah in the same file -- ie Microsoft... does not deal with case that \n is inside a field
    QStringList rtrn;
    QString str = contents.at(row);
    QString tmp = "";
    bool start_quote = false;
    bool in_field = false;
    for(int i = 0; i < str.size(); i++) {
       if(!in_field && str.at(i) == '\"') {//does this field start with " ?
          in_field = true;
          start_quote = true;
          tmp = "";
          continue;
       }//end if !in_field && str.at(i) == "\""
       if(!in_field && str.at(i) != ',') {//starting a new field and it does not start with "
          in_field = true;
          start_quote = false;
          tmp = str.at(i);
          continue;
       }//end if ! in_field
       if(in_field && start_quote && str.at(i) == '\"' && str.at(i+1) == ',') {//if we are in a field that started with a quote and the next two characters are ",
          in_field = false;
          start_quote = false;
          rtrn.append(tmp);
          tmp = "";
          i++;//skip the , because we have dealt with it
          continue;
       }//end if in_field && start_quote && str.at(i) == "\"" && str.at(i+1) == ","
       if(!start_quote && str.at(i) == ',') {//if we are in a field that did not start with a quote and have hit a comma
          in_field = false;
          rtrn.append(tmp);
          tmp = "";
          continue;
       }//end if in_field && !start_quote && str.at(i) == ","
       tmp += str.at(i);
    }//end for i < size
    rtrn.append(tmp);
    return rtrn;
}

QVariant SxWResult::data( int index ) {
   return row(at()).at(index);
}

bool SxWResult::isNull( int index ) {
    return false;
}

bool SxWResult::reset( const QString& query ) {
    QString data;
    int space = query.indexOf( " " );

    if ( space > -1 ) {
        data = query.mid( space+1 );
    }

    if ( query.startsWith( "select" ) ) {
        if ( at() > -1 ) {
            setAt( -1 );
        }
        setSelect( true );
        setActive( true );
        return true;
    } else
    if ( query.startsWith( "insert" ) ) {
        data.replace( "${AUTO}", QString::number( contents.size() ) );
        contents << data;

        setActive( true );
        return true;
    } else
    if ( query.startsWith( "commit" ) ) {
        foreach( QString buffer, contents ) {
            f->write( buffer.toAscii() + "\n" );
        }
        f->seek( 0 );
        return true;
    } else
    if ( query.startsWith( "delete" ) ) {
        // if no argument given, return false
        if ( data.isEmpty() ) {
            return false;
        }

        int index = data.toInt();
        if ( index > -1 && index < contents.size() ) {
            contents.removeAt( index );
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

bool SxWResult::fetch( int index ) {
    if ( contents.size() == 0 ) {
        return false;
    }

    if ( index >= 0 && index < contents.size()-1 ) {
        setAt( index );
        return true;
    } else {
        return false;
    }
}

bool SxWResult::fetchFirst() {
    setAt( 0 );
    return true;
}

bool SxWResult::fetchNext() {
    if ( contents.size() == 0 ) {
        return false;
    }
    printf( "next called\n" );
    printf( "Size: %d, Pointer: %d\n", contents.size(), at() );
    if ( at() < contents.size()-1 && contents.size() > 0 ) {
        setAt( at()+1 );
        return true;
    } else {
        return false;
    }
}

bool SxWResult::fetchLast() {
    setAt( contents.size()-1 );
    return true;
}

int SxWResult::size() {//query.size always returns -1... why?
   return contents.size();
}

int SxWResult::numRowsAffected() {
    return 0;
}

QSqlRecord SxWResult::record() {
   qDebug("SxWResult::record");
   QSqlRecord rtrn;
   QSqlField fld;
   QStringList trow = row(at());
   for(int i = 0; i < fieldNames.size() && i < trow.size(); i++) {
      fld = QSqlField(fieldNames.at(i), QVariant::String);
      fld.setValue(QVariant(trow.at(i)));
      rtrn.append(fld);
   }//end for i < fieldNames.size()
   return rtrn;
}
