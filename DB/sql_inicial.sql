
CREATE DATABASE foodology
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'en_US.UTF-8'
       LC_CTYPE = 'en_US.UTF-8'
       CONNECTION LIMIT = -1;

--DROP TABLE public.tb_impresora;

-- Table: public.restaurant_location

-- DROP TABLE IF EXISTS public.restaurant_location;

CREATE TABLE IF NOT EXISTS public.restaurant_location
(
    id serial,
    restaurant_name character varying COLLATE pg_catalog."default" NOT NULL,
    store_rappi_id character varying COLLATE pg_catalog."default" NOT NULL,
    store_rappi_address character varying COLLATE pg_catalog."default" NOT NULL,
    created_date timestamp without time zone NOT NULL DEFAULT (substr((now())::text, 0, 20))::timestamp without time zone,
    open_time time without time zone,
    close_time time without time zone,
    lat double precision,
    lng double precision,
    CONSTRAINT restaurant_location_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.restaurant_location
    OWNER to postgres;

GRANT ALL ON TABLE public.restaurant_location TO postgres;

