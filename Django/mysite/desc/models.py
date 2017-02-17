import datetime

from django.db import models

class Products(models.Model):
    fullname 	= models.CharField(max_length=250, db_index=True, null=True, blank=True)
    guid        = models.CharField(max_length=36, db_index=True, null=True, blank=True)
    code 		= models.CharField(max_length=15, db_index=True, null=True, blank=True)
    isbn 		= models.CharField(max_length=30, db_index=True, null=True, blank=True)
    article 	= models.CharField(max_length=15, blank=True)
    year 		= models.IntegerField(default=0)
    tema 		= models.CharField(max_length=100, db_index=True, null=True, blank=True)
    serya 		= models.CharField(max_length=300, db_index=True, null=True, blank=True)
    age 		= models.IntegerField(default=0)
    authors 	= models.CharField(max_length=300, db_index=True, null=True, blank=True)
    listeditors = models.CharField(max_length=200, null=True, blank=True)
    country 	= models.CharField(max_length=50, null=True, blank=True)
    cover 		= models.CharField(max_length=30, null=True, blank=True)
    pages 		= models.IntegerField(default=0)
    superobl 	= models.BooleanField()
    volnodiv 	= models.BooleanField()	
    size_x 		= models.IntegerField(default=0)
    size_y 		= models.IntegerField(default=0)
    size_z 		= models.IntegerField(default=0)
    format 		= models.CharField(max_length=20, blank=True)
    weigth 		= models.IntegerField(default=0)
    prim 		= models.TextField(blank=True)
    paper_type  = models.CharField(max_length=30, null=True, blank=True)
    sex         = models.CharField(max_length=30, null=True, blank=True)
    decor       = models.CharField(max_length=50, null=True, blank=True)
    class Meta:
        ordering = ["fullname"]
    def __str__(self):
        return self.fullname        
	
class Barcodes(models.Model):
    product 	= models.ForeignKey(Products, on_delete=models.CASCADE)
    barcode 	= models.CharField(max_length=25, db_index=True, null=True, blank=True)
    def __str__(self):
        return self.barcode
	
class Prices(models.Model):
    product 	= models.ForeignKey(Products, on_delete=models.CASCADE)
    price 		= models.FloatField(default=0)
    quantity    = models.IntegerField(default=0)
    shop_id     = models.IntegerField(default=0)
    shop_name   = models.CharField(max_length=50, null=True, blank=True)
    def __str__(self):
        return self.shop_name
		