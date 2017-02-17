from django.conf.urls import url

from . import views

urlpatterns = [
    #url(r'^$', views.IndexView.as_view(), name='index'),
    url(r'^$', views.index, name='index'),
    url(r'^product/(?P<pk>[0-9]+)/$', views.DetailView.as_view(), name='detail'),
	url(r'^filter/$', views.filter, name='filter'),
]