from celery import Celery

celery = Celery(
    'app',
    backend='rpc://',
    broker='pyamqp://guest@localhost//'
)
