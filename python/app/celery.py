from celery import Celery

def make_celery(app):
    celery = Celery(
        app.import_name,
        backend='rpc://',
        broker='pyamqp://guest@localhost//'
    )
    celery.conf.update(app.config)
    return celery





