#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <sys/dir.h>
#include <sys/param.h>
#include "terminalio.c"

/* MACROS */
#define MAX_XDG_DATA_DIRS 10
#define FALSE 0
#define TRUE 1
#define DYNAMIC_STRUCT(v) if((v = malloc(sizeof(*v))) == NULL) eprint("Memory allocation error.\n");
#define DYNAMIC_STRING(o,i) if((o = malloc(strlen(i))) == NULL) eprint("Memory allocation error.\n");
#define DYNAMIC_STRING_COPY(o,i) DYNAMIC_STRING(o,i); strcpy(o,i);
#define ADD_IF_CAT_P(a,n,c) if(strcmp(a,#c) == 0) {if(c != NULL) c->previousNode = n; n->nextNode = c; c = n; return;}
#define ADD_IF_CAT_A(a,n,c) if(strcmp(a,#c) == 0) {if(c != NULL) c->previousNode = n; n->nextNode = c; n->item->hasAdditionalCategory = TRUE; c = n; return;}

/* TYPES */
typedef char BOOL;

typedef struct MenuItem MenuItem;
struct MenuItem {
        char *title, *command;
        BOOL hasAdditionalCategory;
        /*Menu *categories[MAX_ITEM_CATEGORIES];*/
        /* OnlyShowIn */
	/* Icon */
};

typedef struct Menu Menu;
struct Menu {
        MenuItem *item;
        Menu *nextNode;
	Menu *previousNode;
};

/* NON-CATEGORY GLOBALS */
Menu *current_menu_item = NULL;
int current_menu_category = 0;
Menu *current_menu_head = NULL;
Menu *previous_menu_head = NULL;

/* FUNCTIONS */
void eprint(const char *errstr, ...) {
        vfprintf(stderr, errstr, NULL);
        exit(EXIT_FAILURE);
}

void add_additional_categories();
void add_to_cat(char *cat, MenuItem *item, BOOL acat);
int file_select(struct direct *entry);
void parse_desktop_file(char *path);
char** get_xdg_data_dirs();
void read_xdg_menu();
void draw_menu(BOOL acat);
void menu_next();
void menu_prev();
void menu_go();
int main(int argc, char *argv[]);
