--
-- PostgreSQL database dump
--

-- Dumped from database version 14.17 (Homebrew)
-- Dumped by pg_dump version 14.17 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: generate_seo_slug(text, text, text, text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.generate_seo_slug(page_type text, province_slug text DEFAULT NULL::text, district_slug text DEFAULT NULL::text, service_slug text DEFAULT NULL::text, portfolio_slug text DEFAULT NULL::text) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    CASE page_type
        WHEN 'province' THEN
            RETURN 'locations/' || province_slug;
        WHEN 'district' THEN
            RETURN 'locations/' || province_slug || '/' || district_slug;
        WHEN 'service' THEN
            RETURN 'services/' || service_slug;
        WHEN 'portfolio' THEN
            RETURN 'portfolio/' || portfolio_slug;
        ELSE
            RAISE EXCEPTION 'Invalid page type: %', page_type;
    END CASE;
END;
$$;


--
-- Name: get_variation_block_by_hash(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.get_variation_block_by_hash(block_type_param text, hash_input text) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
    block_count INTEGER;
    selected_index INTEGER;
    result_text TEXT;
BEGIN
    -- Get total count of blocks for this type
    SELECT COUNT(*) INTO block_count
    FROM seo_variation_blocks
    WHERE block_type = block_type_param;
    
    IF block_count = 0 THEN
        RETURN '';
    END IF;
    
    -- Generate deterministic index based on hash
    selected_index := (hashtext(hash_input) % block_count);
    
    -- Get the selected variation block
    SELECT variant_md INTO result_text
    FROM seo_variation_blocks
    WHERE block_type = block_type_param
    ORDER BY id
    LIMIT 1 OFFSET selected_index;
    
    RETURN COALESCE(result_text, '');
END;
$$;


--
-- Name: is_admin(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.is_admin() RETURNS boolean
    LANGUAGE plpgsql SECURITY DEFINER
    AS $$
BEGIN
    RETURN (
        auth.role() = 'authenticated' AND 
        auth.jwt() ->> 'role' = 'admin'
    );
END;
$$;


--
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;


--
-- Name: upsert_seo_page(text, uuid, uuid, uuid, uuid); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.upsert_seo_page(page_type text, province_id_param uuid DEFAULT NULL::uuid, district_id_param uuid DEFAULT NULL::uuid, service_id_param uuid DEFAULT NULL::uuid, portfolio_id_param uuid DEFAULT NULL::uuid) RETURNS uuid
    LANGUAGE plpgsql
    AS $$
DECLARE
    page_id UUID;
    page_slug TEXT;
    page_title TEXT;
    page_meta_description TEXT;
    page_h1 TEXT;
    page_content TEXT;
    page_faq JSONB;
    province_name TEXT;
    district_name TEXT;
    service_name TEXT;
    portfolio_title TEXT;
    hash_input TEXT;
BEGIN
    -- Get related entity names
    IF province_id_param IS NOT NULL THEN
        SELECT name INTO province_name FROM locations_province WHERE id = province_id_param;
    END IF;
    
    IF district_id_param IS NOT NULL THEN
        SELECT name INTO district_name FROM locations_district WHERE id = district_id_param;
    END IF;
    
    IF service_id_param IS NOT NULL THEN
        SELECT name INTO service_name FROM services WHERE id = service_id_param;
    END IF;
    
    IF portfolio_id_param IS NOT NULL THEN
        SELECT title INTO portfolio_title FROM portfolio_projects WHERE id = portfolio_id_param;
    END IF;
    
    -- Generate slug
    page_slug := generate_seo_slug(
        page_type,
        (SELECT slug FROM locations_province WHERE id = province_id_param),
        (SELECT slug FROM locations_district WHERE id = district_id_param),
        (SELECT slug FROM services WHERE id = service_id_param),
        (SELECT slug FROM portfolio_projects WHERE id = portfolio_id_param)
    );
    
    -- Create hash input for deterministic variation selection
    hash_input := COALESCE(province_name, '') || '_' || COALESCE(district_name, '') || '_' || COALESCE(service_name, '') || '_' || COALESCE(portfolio_title, '');
    
    -- Generate content based on page type
    CASE page_type
        WHEN 'province' THEN
            page_title := province_name || ' Mekan Fotoğrafçısı | Profesyonel Mimari ve İç Mekan Fotoğrafçılığı';
            page_meta_description := province_name || '''da profesyonel mekan fotoğrafçılığı hizmetleri. Mimari, iç mekan, emlak ve otel fotoğrafçılığı için uzman ekibimizle iletişime geçin.';
            page_h1 := province_name || ' Mekan Fotoğrafçısı';
            
        WHEN 'district' THEN
            page_title := district_name || ', ' || province_name || ' Mekan Fotoğrafçısı | Profesyonel Fotoğrafçılık Hizmetleri';
            page_meta_description := district_name || ', ' || province_name || '''da mekan fotoğrafçılığı. Mimari, iç mekan, emlak ve otel fotoğrafları için profesyonel hizmet.';
            page_h1 := district_name || ' Mekan Fotoğrafçısı';
            
        WHEN 'service' THEN
            page_title := service_name || ' | Antalya ve Muğla''da Profesyonel Hizmet';
            page_meta_description := 'Antalya ve Muğla bölgesinde ' || service_name || ' hizmetleri. Profesyonel ekipman ve deneyimli ekibimizle kaliteli sonuçlar.';
            page_h1 := service_name;
            
        WHEN 'portfolio' THEN
            page_title := portfolio_title || ' | Mekan Fotoğrafçısı Portfolio';
            page_meta_description := portfolio_title || ' projesi detayları. Profesyonel mekan fotoğrafçılığı örnekleri ve çalışma sürecimiz.';
            page_h1 := portfolio_title;
    END CASE;
    
    -- Generate content using variation blocks
    page_content := get_variation_block_by_hash('intro', hash_input || '_intro') || E'\n\n' ||
                   get_variation_block_by_hash('process', hash_input || '_process') || E'\n\n' ||
                   get_variation_block_by_hash('benefits', hash_input || '_benefits') || E'\n\n' ||
                   get_variation_block_by_hash('cta', hash_input || '_cta');
    
    -- Generate FAQ JSON
    page_faq := jsonb_build_object(
        'questions', jsonb_build_array(
            jsonb_build_object(
                'question', 'Hangi bölgelerde hizmet veriyorsunuz?',
                'answer', 'Antalya ve Muğla bölgesinin tamamında, özellikle ' || COALESCE(district_name, province_name) || ' ve çevresinde profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.'
            ),
            jsonb_build_object(
                'question', 'Fotoğraf çekimi ne kadar sürer?',
                'answer', 'Mekanın büyüklüğüne ve çekim türüne göre değişmekle birlikte, ortalama 2-4 saat sürmektedir.'
            ),
            jsonb_build_object(
                'question', 'Fotoğraflar ne zaman teslim edilir?',
                'answer', 'Çekim sonrası 3-5 iş günü içinde düzenlenmiş fotoğraflarınızı teslim ediyoruz.'
            )
        )
    );
    
    -- Upsert the SEO page
    INSERT INTO seo_pages (
        type, province_id, district_id, service_id, portfolio_id,
        slug, title, meta_description, h1, content_md, faq_json, published
    ) VALUES (
        page_type, province_id_param, district_id_param, service_id_param, portfolio_id_param,
        page_slug, page_title, page_meta_description, page_h1, page_content, page_faq, false
    )
    ON CONFLICT (slug) DO UPDATE SET
        title = EXCLUDED.title,
        meta_description = EXCLUDED.meta_description,
        h1 = EXCLUDED.h1,
        content_md = EXCLUDED.content_md,
        faq_json = EXCLUDED.faq_json,
        updated_at = NOW()
    RETURNING id INTO page_id;
    
    RETURN page_id;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: admin_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.admin_users (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    name character varying(100),
    is_active boolean DEFAULT true,
    last_login_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: locations_district; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.locations_district (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    province_id uuid NOT NULL,
    name character varying(100) NOT NULL,
    slug character varying(100) NOT NULL,
    is_active boolean DEFAULT false,
    local_notes text,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    description text,
    content text,
    image character varying(500)
);


--
-- Name: COLUMN locations_district.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.locations_district.description IS 'Detailed description for district detail pages';


--
-- Name: COLUMN locations_district.content; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.locations_district.content IS 'Markdown content for district detail pages';


--
-- Name: COLUMN locations_district.image; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.locations_district.image IS 'Main image URL for district detail pages';


--
-- Name: locations_province; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.locations_province (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(100) NOT NULL,
    slug character varying(100) NOT NULL,
    region_name character varying(50),
    plate_code integer,
    is_active boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    description text,
    content text,
    image character varying(500)
);


--
-- Name: COLUMN locations_province.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.locations_province.description IS 'Detailed description for province detail pages';


--
-- Name: COLUMN locations_province.content; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.locations_province.content IS 'Markdown content for province detail pages';


--
-- Name: COLUMN locations_province.image; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.locations_province.image IS 'Main image URL for province detail pages';


--
-- Name: locations_town; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.locations_town (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    district_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    is_active boolean DEFAULT true,
    created_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at timestamp with time zone DEFAULT timezone('utc'::text, now()) NOT NULL
);


--
-- Name: media; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.media (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    storage_path character varying(500) NOT NULL,
    public_url character varying(500) NOT NULL,
    alt character varying(200),
    width integer,
    height integer,
    file_size integer,
    mime_type character varying(100),
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    folder_id uuid
);


--
-- Name: media_folders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.media_folders (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    name character varying(255) NOT NULL,
    parent_id uuid,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: portfolio_projects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.portfolio_projects (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    title character varying(200) NOT NULL,
    slug character varying(200) NOT NULL,
    province_id uuid,
    district_id uuid,
    cover_media_id uuid,
    gallery_media_ids uuid[] DEFAULT '{}'::uuid[],
    description text,
    year integer,
    is_published boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: post_meta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.post_meta (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    post_id uuid NOT NULL,
    meta_key character varying(255) NOT NULL,
    meta_value jsonb,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: posts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.posts (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    title character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    content text,
    excerpt text,
    post_type character varying(50) DEFAULT 'page'::character varying NOT NULL,
    post_status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    parent_id uuid,
    menu_order integer DEFAULT 0,
    author_id uuid,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    gallery_folder_id uuid
);


--
-- Name: quotes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.quotes (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(50),
    location character varying(255),
    service character varying(255),
    message text,
    wizard_details jsonb,
    ip_address character varying(50),
    is_read boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    status character varying(50) DEFAULT 'beklemede'::character varying,
    admin_note text
);


--
-- Name: quotes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.quotes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: quotes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.quotes_id_seq OWNED BY public.quotes.id;


--
-- Name: schema_migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.schema_migrations (
    version character varying(255) NOT NULL,
    applied_at timestamp with time zone DEFAULT now()
);


--
-- Name: seo_pages; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.seo_pages (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    type character varying(20) NOT NULL,
    province_id uuid,
    district_id uuid,
    service_id uuid,
    portfolio_id uuid,
    slug character varying(300) NOT NULL,
    title character varying(200) NOT NULL,
    meta_description character varying(320) NOT NULL,
    h1 character varying(200) NOT NULL,
    content_md text NOT NULL,
    faq_json jsonb,
    published boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    CONSTRAINT seo_pages_type_check CHECK (((type)::text = ANY ((ARRAY['province'::character varying, 'district'::character varying, 'service'::character varying, 'portfolio'::character varying])::text[])))
);


--
-- Name: seo_templates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.seo_templates (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    type character varying(20) NOT NULL,
    base_template_md text NOT NULL,
    rules_json jsonb,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    CONSTRAINT seo_templates_type_check CHECK (((type)::text = ANY ((ARRAY['province'::character varying, 'district'::character varying, 'service'::character varying, 'portfolio'::character varying])::text[])))
);


--
-- Name: seo_variation_blocks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.seo_variation_blocks (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    block_type character varying(20) NOT NULL,
    variant_md text NOT NULL,
    weight integer DEFAULT 1,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    CONSTRAINT seo_variation_blocks_block_type_check CHECK (((block_type)::text = ANY ((ARRAY['intro'::character varying, 'process'::character varying, 'benefits'::character varying, 'faq'::character varying, 'cta'::character varying])::text[])))
);


--
-- Name: services; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.services (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(200) NOT NULL,
    slug character varying(200) NOT NULL,
    short_intro text,
    is_active boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    description text,
    content text,
    image character varying(500),
    gallery_images jsonb DEFAULT '[]'::jsonb
);


--
-- Name: COLUMN services.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.services.description IS 'Detailed description for service detail pages';


--
-- Name: COLUMN services.content; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.services.content IS 'Markdown content for service detail pages';


--
-- Name: COLUMN services.image; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.services.image IS 'Main image URL for service detail pages';


--
-- Name: COLUMN services.gallery_images; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.services.gallery_images IS 'Array of image URLs (e.g., Pexels links) for gallery display on service detail pages';


--
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.settings (
    key character varying(255) NOT NULL,
    value text,
    "group" character varying(50) DEFAULT 'general'::character varying NOT NULL,
    type character varying(50) DEFAULT 'text'::character varying NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: taxonomies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taxonomies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(100) NOT NULL,
    slug character varying(100) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: term_relationships; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.term_relationships (
    post_id uuid NOT NULL,
    term_id uuid NOT NULL
);


--
-- Name: terms; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.terms (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    taxonomy_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    parent_id uuid,
    description text,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: quotes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotes ALTER COLUMN id SET DEFAULT nextval('public.quotes_id_seq'::regclass);


--
-- Name: admin_users admin_users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_email_key UNIQUE (email);


--
-- Name: admin_users admin_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_pkey PRIMARY KEY (id);


--
-- Name: locations_district locations_district_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_district
    ADD CONSTRAINT locations_district_pkey PRIMARY KEY (id);


--
-- Name: locations_district locations_district_province_id_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_district
    ADD CONSTRAINT locations_district_province_id_slug_key UNIQUE (province_id, slug);


--
-- Name: locations_province locations_province_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_province
    ADD CONSTRAINT locations_province_pkey PRIMARY KEY (id);


--
-- Name: locations_province locations_province_plate_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_province
    ADD CONSTRAINT locations_province_plate_code_key UNIQUE (plate_code);


--
-- Name: locations_province locations_province_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_province
    ADD CONSTRAINT locations_province_slug_key UNIQUE (slug);


--
-- Name: locations_town locations_town_district_id_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_town
    ADD CONSTRAINT locations_town_district_id_slug_key UNIQUE (district_id, slug);


--
-- Name: locations_town locations_town_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_town
    ADD CONSTRAINT locations_town_pkey PRIMARY KEY (id);


--
-- Name: media_folders media_folders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media_folders
    ADD CONSTRAINT media_folders_pkey PRIMARY KEY (id);


--
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- Name: portfolio_projects portfolio_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portfolio_projects
    ADD CONSTRAINT portfolio_projects_pkey PRIMARY KEY (id);


--
-- Name: portfolio_projects portfolio_projects_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portfolio_projects
    ADD CONSTRAINT portfolio_projects_slug_key UNIQUE (slug);


--
-- Name: post_meta post_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_meta
    ADD CONSTRAINT post_meta_pkey PRIMARY KEY (id);


--
-- Name: post_meta post_meta_post_id_meta_key_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_meta
    ADD CONSTRAINT post_meta_post_id_meta_key_key UNIQUE (post_id, meta_key);


--
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);


--
-- Name: posts posts_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_slug_key UNIQUE (slug);


--
-- Name: quotes quotes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotes
    ADD CONSTRAINT quotes_pkey PRIMARY KEY (id);


--
-- Name: schema_migrations schema_migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.schema_migrations
    ADD CONSTRAINT schema_migrations_pkey PRIMARY KEY (version);


--
-- Name: seo_pages seo_pages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_pages
    ADD CONSTRAINT seo_pages_pkey PRIMARY KEY (id);


--
-- Name: seo_pages seo_pages_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_pages
    ADD CONSTRAINT seo_pages_slug_key UNIQUE (slug);


--
-- Name: seo_templates seo_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_templates
    ADD CONSTRAINT seo_templates_pkey PRIMARY KEY (id);


--
-- Name: seo_variation_blocks seo_variation_blocks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_variation_blocks
    ADD CONSTRAINT seo_variation_blocks_pkey PRIMARY KEY (id);


--
-- Name: services services_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_pkey PRIMARY KEY (id);


--
-- Name: services services_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_slug_key UNIQUE (slug);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (key);


--
-- Name: taxonomies taxonomies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxonomies
    ADD CONSTRAINT taxonomies_pkey PRIMARY KEY (id);


--
-- Name: taxonomies taxonomies_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxonomies
    ADD CONSTRAINT taxonomies_slug_key UNIQUE (slug);


--
-- Name: term_relationships term_relationships_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.term_relationships
    ADD CONSTRAINT term_relationships_pkey PRIMARY KEY (post_id, term_id);


--
-- Name: terms terms_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.terms
    ADD CONSTRAINT terms_pkey PRIMARY KEY (id);


--
-- Name: terms terms_taxonomy_id_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.terms
    ADD CONSTRAINT terms_taxonomy_id_slug_key UNIQUE (taxonomy_id, slug);


--
-- Name: idx_admin_users_email; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_admin_users_email ON public.admin_users USING btree (email);


--
-- Name: idx_locations_district_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_district_active ON public.locations_district USING btree (is_active);


--
-- Name: idx_locations_district_province; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_district_province ON public.locations_district USING btree (province_id);


--
-- Name: idx_locations_district_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_district_slug ON public.locations_district USING btree (slug);


--
-- Name: idx_locations_province_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_province_active ON public.locations_province USING btree (is_active);


--
-- Name: idx_locations_province_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_province_slug ON public.locations_province USING btree (slug);


--
-- Name: idx_locations_town_district_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_town_district_id ON public.locations_town USING btree (district_id);


--
-- Name: idx_locations_town_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_locations_town_slug ON public.locations_town USING btree (slug);


--
-- Name: idx_media_folder_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_media_folder_id ON public.media USING btree (folder_id);


--
-- Name: idx_media_folders_parent_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_media_folders_parent_id ON public.media_folders USING btree (parent_id);


--
-- Name: idx_portfolio_published; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_portfolio_published ON public.portfolio_projects USING btree (is_published);


--
-- Name: idx_portfolio_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_portfolio_slug ON public.portfolio_projects USING btree (slug);


--
-- Name: idx_post_meta_key; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_post_meta_key ON public.post_meta USING btree (meta_key);


--
-- Name: idx_post_meta_lookup; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_post_meta_lookup ON public.post_meta USING btree (post_id, meta_key);


--
-- Name: idx_posts_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_posts_slug ON public.posts USING btree (slug);


--
-- Name: idx_posts_type_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_posts_type_status ON public.posts USING btree (post_type, post_status);


--
-- Name: idx_seo_pages_district; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_seo_pages_district ON public.seo_pages USING btree (district_id);


--
-- Name: idx_seo_pages_province; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_seo_pages_province ON public.seo_pages USING btree (province_id);


--
-- Name: idx_seo_pages_published; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_seo_pages_published ON public.seo_pages USING btree (published);


--
-- Name: idx_seo_pages_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_seo_pages_slug ON public.seo_pages USING btree (slug);


--
-- Name: idx_seo_pages_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_seo_pages_type ON public.seo_pages USING btree (type);


--
-- Name: idx_services_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_services_active ON public.services USING btree (is_active);


--
-- Name: idx_services_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_services_slug ON public.services USING btree (slug);


--
-- Name: idx_variation_blocks_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_variation_blocks_type ON public.seo_variation_blocks USING btree (block_type);


--
-- Name: admin_users update_admin_users_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_admin_users_updated_at BEFORE UPDATE ON public.admin_users FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: locations_district update_locations_district_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_locations_district_updated_at BEFORE UPDATE ON public.locations_district FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: locations_province update_locations_province_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_locations_province_updated_at BEFORE UPDATE ON public.locations_province FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: media update_media_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_media_updated_at BEFORE UPDATE ON public.media FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: portfolio_projects update_portfolio_projects_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_portfolio_projects_updated_at BEFORE UPDATE ON public.portfolio_projects FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: post_meta update_post_meta_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_post_meta_updated_at BEFORE UPDATE ON public.post_meta FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: posts update_posts_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_posts_updated_at BEFORE UPDATE ON public.posts FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: seo_pages update_seo_pages_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_seo_pages_updated_at BEFORE UPDATE ON public.seo_pages FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: seo_templates update_seo_templates_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_seo_templates_updated_at BEFORE UPDATE ON public.seo_templates FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: seo_variation_blocks update_seo_variation_blocks_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_seo_variation_blocks_updated_at BEFORE UPDATE ON public.seo_variation_blocks FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: services update_services_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER update_services_updated_at BEFORE UPDATE ON public.services FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: media_folders fk_parent; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.media_folders
    ADD CONSTRAINT fk_parent FOREIGN KEY (parent_id) REFERENCES public.media_folders(id) ON DELETE CASCADE;


--
-- Name: locations_district locations_district_province_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_district
    ADD CONSTRAINT locations_district_province_id_fkey FOREIGN KEY (province_id) REFERENCES public.locations_province(id) ON DELETE CASCADE;


--
-- Name: locations_town locations_town_district_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.locations_town
    ADD CONSTRAINT locations_town_district_id_fkey FOREIGN KEY (district_id) REFERENCES public.locations_district(id) ON DELETE CASCADE;


--
-- Name: portfolio_projects portfolio_projects_cover_media_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portfolio_projects
    ADD CONSTRAINT portfolio_projects_cover_media_id_fkey FOREIGN KEY (cover_media_id) REFERENCES public.media(id);


--
-- Name: portfolio_projects portfolio_projects_district_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portfolio_projects
    ADD CONSTRAINT portfolio_projects_district_id_fkey FOREIGN KEY (district_id) REFERENCES public.locations_district(id);


--
-- Name: portfolio_projects portfolio_projects_province_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.portfolio_projects
    ADD CONSTRAINT portfolio_projects_province_id_fkey FOREIGN KEY (province_id) REFERENCES public.locations_province(id);


--
-- Name: post_meta post_meta_post_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.post_meta
    ADD CONSTRAINT post_meta_post_id_fkey FOREIGN KEY (post_id) REFERENCES public.posts(id) ON DELETE CASCADE;


--
-- Name: posts posts_gallery_folder_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_gallery_folder_id_fkey FOREIGN KEY (gallery_folder_id) REFERENCES public.media_folders(id) ON DELETE SET NULL;


--
-- Name: posts posts_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.posts(id) ON DELETE SET NULL;


--
-- Name: seo_pages seo_pages_district_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_pages
    ADD CONSTRAINT seo_pages_district_id_fkey FOREIGN KEY (district_id) REFERENCES public.locations_district(id);


--
-- Name: seo_pages seo_pages_portfolio_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_pages
    ADD CONSTRAINT seo_pages_portfolio_id_fkey FOREIGN KEY (portfolio_id) REFERENCES public.portfolio_projects(id);


--
-- Name: seo_pages seo_pages_province_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_pages
    ADD CONSTRAINT seo_pages_province_id_fkey FOREIGN KEY (province_id) REFERENCES public.locations_province(id);


--
-- Name: seo_pages seo_pages_service_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.seo_pages
    ADD CONSTRAINT seo_pages_service_id_fkey FOREIGN KEY (service_id) REFERENCES public.services(id);


--
-- Name: term_relationships term_relationships_post_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.term_relationships
    ADD CONSTRAINT term_relationships_post_id_fkey FOREIGN KEY (post_id) REFERENCES public.posts(id) ON DELETE CASCADE;


--
-- Name: term_relationships term_relationships_term_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.term_relationships
    ADD CONSTRAINT term_relationships_term_id_fkey FOREIGN KEY (term_id) REFERENCES public.terms(id) ON DELETE CASCADE;


--
-- Name: terms terms_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.terms
    ADD CONSTRAINT terms_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.terms(id) ON DELETE SET NULL;


--
-- Name: terms terms_taxonomy_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.terms
    ADD CONSTRAINT terms_taxonomy_id_fkey FOREIGN KEY (taxonomy_id) REFERENCES public.taxonomies(id) ON DELETE CASCADE;


--
-- Name: locations_district Admin full access to districts; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to districts" ON public.locations_district USING (public.is_admin());


--
-- Name: media Admin full access to media; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to media" ON public.media USING (public.is_admin());


--
-- Name: portfolio_projects Admin full access to portfolio; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to portfolio" ON public.portfolio_projects USING (public.is_admin());


--
-- Name: locations_province Admin full access to provinces; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to provinces" ON public.locations_province USING (public.is_admin());


--
-- Name: seo_pages Admin full access to seo pages; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to seo pages" ON public.seo_pages USING (public.is_admin());


--
-- Name: seo_templates Admin full access to seo templates; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to seo templates" ON public.seo_templates USING (public.is_admin());


--
-- Name: services Admin full access to services; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to services" ON public.services USING (public.is_admin());


--
-- Name: seo_variation_blocks Admin full access to variation blocks; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Admin full access to variation blocks" ON public.seo_variation_blocks USING (public.is_admin());


--
-- Name: locations_district Public can read active districts; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read active districts" ON public.locations_district FOR SELECT USING ((is_active = true));


--
-- Name: locations_province Public can read active provinces; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read active provinces" ON public.locations_province FOR SELECT USING ((is_active = true));


--
-- Name: services Public can read active services; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read active services" ON public.services FOR SELECT USING ((is_active = true));


--
-- Name: media Public can read media; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read media" ON public.media FOR SELECT USING (true);


--
-- Name: portfolio_projects Public can read published portfolio; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read published portfolio" ON public.portfolio_projects FOR SELECT USING ((is_published = true));


--
-- Name: seo_pages Public can read published seo pages; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read published seo pages" ON public.seo_pages FOR SELECT USING ((published = true));


--
-- Name: seo_templates Public can read seo templates; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read seo templates" ON public.seo_templates FOR SELECT USING (true);


--
-- Name: seo_variation_blocks Public can read variation blocks; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY "Public can read variation blocks" ON public.seo_variation_blocks FOR SELECT USING (true);


--
-- Name: locations_district; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.locations_district ENABLE ROW LEVEL SECURITY;

--
-- Name: locations_province; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.locations_province ENABLE ROW LEVEL SECURITY;

--
-- Name: media; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.media ENABLE ROW LEVEL SECURITY;

--
-- Name: portfolio_projects; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.portfolio_projects ENABLE ROW LEVEL SECURITY;

--
-- Name: seo_pages; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.seo_pages ENABLE ROW LEVEL SECURITY;

--
-- Name: seo_templates; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.seo_templates ENABLE ROW LEVEL SECURITY;

--
-- Name: seo_variation_blocks; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.seo_variation_blocks ENABLE ROW LEVEL SECURITY;

--
-- Name: services; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.services ENABLE ROW LEVEL SECURITY;

--
-- PostgreSQL database dump complete
--

