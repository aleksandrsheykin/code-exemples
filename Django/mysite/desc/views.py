from django.shortcuts import get_object_or_404, render

from django.http import HttpResponse
from django.http import HttpResponseRedirect
from django.template import RequestContext, loader
from django.core.urlresolvers import reverse
from django.views import generic
from django.db.models import Q

from django.db.models import Aggregate
from django.db import models

from .models import Products, Barcodes, Prices

from django.core.paginator import Paginator

STRONPAGE = 25  #строк на странице (для пагинатора)
"""class IndexView(generic.ListView):
    template_name = 'desc/list.html'
    context_object_name = 'product_list'
    paginate_by = 2

    def get_queryset(self):
        output = Products.objects \
            .filter(Q(prices__shop_id=1) | Q(prices__shop_id__isnull=True)) \
            .values('id','fullname', 'authors', 'code', 'tema', 'prices__price') \
            .annotate(barcodes=Concat('barcodes__barcode', True))
        #output = filter2(self.request)
        
        return output"""       
    
def index(request):
    output = Products.objects \
        .filter(Q(prices__shop_id=1) | Q(prices__shop_id__isnull=True)) \
        .values('id','fullname', 'authors', 'code', 'tema', 'prices__price', 'prices__quantity') \
        .annotate(barcodes=Concat('barcodes__barcode', True))
            
    try:
        page_num = request.GET["page"]
    except KeyError:
        page_num = 1
    paginator = Paginator(output, STRONPAGE)
    output = paginator.page(page_num)

    template = loader.get_template('desc/list.html')
    context = RequestContext(request, {
        'product_list': output,	'request_post': request.POST})
    return HttpResponse(template.render(context))

class DetailView(generic.DetailView):
    model = Products
    template_name = 'desc/detail.html'
 
    def get_context_data(self, **kwargs):
        context = super(DetailView, self).get_context_data(**kwargs)
        context['prices'] = Prices.objects.filter(product=self.kwargs.get('pk', None), shop_id=1).values('price', 'quantity')
        return context


def filter(request):
    try:
        request_text = request.POST.get('search_input', False)
        if (request_text.isspace() or request_text == ''):          #пустой поисковый запрос
            output = Products.objects \
                .filter(Q(prices__shop_id=1) | Q(prices__shop_id__isnull=True)) \
                .values('id','fullname', 'authors', 'code', 'tema', 'prices__price', 'prices__quantity') \
                .annotate(barcodes=Concat('barcodes__barcode', True))
        else:
            if (len(request_text) > 9 and request_text.isdigit()):  #ищем по штрих-кодам
                #output = Products.objects.values('id', 'fullname', 'authors', 'code', 'tema').filter(barcodes__barcode = request_text).annotate(barcodes=Concat('barcodes__barcode'))
                output = Products.objects \
                    .filter((Q(prices__shop_id=1) | Q(prices__shop_id__isnull=True)), Q(barcodes__barcode = request_text)) \
                    .values('id','fullname', 'authors', 'code', 'tema', 'prices__price', 'prices__quantity') \
                    .annotate(barcodes=Concat('barcodes__barcode', True))
            else:                                                   #по всем остальным полям
                output = Products.objects \
                    .filter((Q(prices__shop_id=1) | Q(prices__shop_id__isnull=True)), 
                            Q(fullname__icontains = request_text) |
                            Q(authors__icontains = request_text) |
                            Q(prim__icontains = request_text) |
                            Q(listeditors__icontains = request_text) |
                            Q(isbn = request_text) |
                            Q(code = request_text) |
                            Q(tema__icontains = request_text) |
                            Q(serya__icontains = request_text)) \
                    .values('id', 'fullname', 'authors', 'code', 'tema', 'prices__price', 'prices__quantity') \
                    .annotate(barcodes=Concat('barcodes__barcode'))

    except BaseException:
        output = Products.objects \
            .filter(Q(prices__shop_id=1) | Q(prices__shop_id__isnull=True)) \
            .values('id','fullname', 'authors', 'code', 'tema', 'prices__price', 'prices__quantity') \
            .annotate(barcodes=Concat('barcodes__barcode', True))
            
    try:
        page_num = request.POST["page"]
    except KeyError:
        page_num = 1
    paginator = Paginator(output, STRONPAGE)
    output = paginator.page(page_num)

    template = loader.get_template('desc/list.html')
    context = RequestContext(request, {
        'product_list': output,	'request_post': request.POST})
    return HttpResponse(template.render(context))
    
class Concat(Aggregate):
    # supports COUNT(distinct field)
    function = 'GROUP_CONCAT'
    template = '%(function)s(%(distinct)s%(expressions)s)'
    def __init__(self, expression, distinct=False, **extra):
        super(Concat, self).__init__(
            expression,
            distinct='DISTINCT ' if distinct else '',
            output_field=models.CharField(),
            **extra)    