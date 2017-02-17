from django.contrib import admin

from .models import Products, Barcodes, Prices

class PricesInline(admin.TabularInline):
	model = Prices
	extra = 2

class BarcodesInline(admin.TabularInline):
	model = Barcodes
	extra = 2

class ProductsAdmin(admin.ModelAdmin):
    fieldsets = [
        (None,     {'fields': ['fullname', 'code', 'authors', 'isbn', 'tema', 'article', 'year', 'serya', 'age', 'listeditors', 'country', 'cover', 'pages', 'superobl', 'volnodiv', 'format', 'weigth', 'prim']}),
        ('Размер', {'fields': ['size_x', 'size_y', 'size_z']}),
    ]
    inlines = [BarcodesInline, PricesInline]
    list_display = ('fullname', 'code', 'authors', 'isbn', 'tema')
    list_filter = ['fullname', 'authors', 'code']

admin.site.register(Products, ProductsAdmin)
