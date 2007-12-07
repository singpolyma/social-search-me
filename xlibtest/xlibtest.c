/* xlibtest.c by Stephen Paul Weber */
/* GPL */

#include <X11/Xlib.h>
#include <X11/Xatom.h>
#include <X11/Xutil.h>
#include <X11/xpm.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <images.h>

void eprint(const char *errstr, ...) {
        vfprintf(stderr, errstr, NULL);
        exit(EXIT_FAILURE);
}

Display* get_x_display() {
	char *display_name = getenv("DISPLAY"); /* address of the X display. */
	if(display_name == NULL || !display_name[0]) display_name = ":0"; /* connect to :0 by default*/
	Display* display = XOpenDisplay(display_name);
	if(display == NULL) eprint("xlibtest: cannot connect to X server.\n");
	return display;
}

void tile_xpm_background(char* xpm, Display* display, Window* win, GC* gc) {
	XImage *image;
	XpmCreateImageFromData(display, xpm, &image, NULL, NULL);
	XPutImage(display, win, gc, image, 0, 0, 0, 0, 200, 500);
}

int main(int argc, char *argv[]) {
	int i;
	for(i = 1; i < argc; i++) /* handle command line arguments */
		if(strncmp(argv[i], "-v", 3) == 0)
			eprint("mnu-"VERSION", (C)opyright 2007, Stephen Paul Weber\n");
		else
			eprint("usage: xlibtest\n"); 

	Display* display = get_x_display();
	int screen_num = DefaultScreen(display);
	Window win = XCreateSimpleWindow(display, RootWindow(display, screen_num),
				0 /*win_x*/, 0 /*win_y*/,
				DisplayWidth(display, screen_num) /*width*/,
				DisplayHeight(display, screen_num) /*height*/,
				0 /*win_border_width*/,
				WhitePixel(display, screen_num),
				BlackPixel(display, screen_num));

	XTextProperty windowName;
	windowName.value    = (unsigned char *) "XlibTest";
	windowName.encoding = XA_STRING;
	windowName.format   = 8;
	windowName.nitems   = strlen((char *) windowName.value);
	XSetWMName(display, win, &windowName);

	XMapWindow(display, win); /* draw window to screen */
	XSync(display, False); /* force x to act on the queue */



GC gc = XCreateGC(display, win, 0, NULL);
if (gc < 0) eprint("Could not get Graphics context.");
XSetForeground(display, gc, WhitePixel(display, screen_num));
XSetBackground(display, gc, BlackPixel(display, screen_num));
XFontStruct* font_info = XLoadQueryFont(display, "-*-helvetica-*-r-normal--20-*");
if(font_info) XSetFont(display, gc, font_info->fid);


tile_xpm_background(smoke_image, display, win, &gc);

XDrawString(display, win, gc, 30, 40, "hello world", strlen("hello world"));
XFlush(display);


XSelectInput(display, win, StructureNotifyMask);
XEvent an_event;
while(display != NULL) {
	XNextEvent(display, &an_event);
	if(an_event.type == DestroyNotify) {
		XCloseDisplay(display);
		exit(0);
	}
}
	
	return 0;
}
