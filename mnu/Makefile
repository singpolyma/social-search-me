# sic - simple irc client
#   (C)opyright MMVI Anselm R. Garbe

include config.mk

SRC = mnu.c
OBJ = ${SRC:.c=.o}

all: options mnu

options:
	@echo mnu build options:
	@echo "CFLAGS   = ${CFLAGS}"
	@echo "LDFLAGS  = ${LDFLAGS}"
	@echo "CC       = ${CC}"
	@echo "LD       = ${LD}"

.c.o:
	@echo CC $<
	@${CC} -c ${CFLAGS} $<

${OBJ}: config.mk

mnu: clean ${OBJ}
	@echo LD $@
	@${LD} -o $@ ${OBJ} ${LDFLAGS}
	@strip $@

clean:
	@echo cleaning
	@rm -f mnu ${OBJ} mnu-${VERSION}.tar.gz

cat:
	@echo generating categories.h
	@./categories.php > categories.h

dist: clean
	@echo creating dist tarball
	@mkdir -p mnu-${VERSION}
	@cp -R Makefile README config.mk mnu.1 ${SRC} mnu-${VERSION}
	@tar -cf mnu-${VERSION}.tar mnu-${VERSION}
	@gzip mnu-${VERSION}.tar
	@rm -rf mnu-${VERSION}

install: all
	@echo installing executable file to ${DESTDIR}${PREFIX}/bin
	@mkdir -p ${DESTDIR}${PREFIX}/bin
	@cp -f mnu ${DESTDIR}${PREFIX}/bin
	@chmod 755 ${DESTDIR}${PREFIX}/bin/mnu
	@echo installing manual page to ${DESTDIR}${MANPREFIX}/man1
	@mkdir -p ${DESTDIR}${MANPREFIX}/man1
	@sed 's/VERSION/${VERSION}/g' < mnu.1 > ${DESTDIR}${MANPREFIX}/man1/mnu.1
	@chmod 644 ${DESTDIR}${MANPREFIX}/man1/mnu.1

uninstall:
	@echo removing executable file from ${DESTDIR}${PREFIX}/bin
	@rm -f ${DESTDIR}${PREFIX}/bin/mnu
	@echo removing manual page from ${DESTDIR}${MANPREFIX}/man1
	@rm -f ${DESTDIR}${MANPREFIX}/man1/mnu.1

.PHONY: all options clean dist install uninstall
