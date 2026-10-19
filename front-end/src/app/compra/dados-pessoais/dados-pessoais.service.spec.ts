import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { environment } from 'src/environments/environment';

import { DadosPessoaisService } from './dados-pessoais.service';

describe('DadosPessoaisService', () => {
  let service: DadosPessoaisService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [DadosPessoaisService],
    });
    service = TestBed.inject(DadosPessoaisService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  it('envia nome como fullName no POST de leads', () => {
    service.salvar({ nome: 'Ana Lima', email: 'ana@example.test' }).subscribe();

    const request = httpMock.expectOne(`${environment.apiUrl}/mkt/leads`);
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ fullName: 'Ana Lima', email: 'ana@example.test' });
    request.flush('', { status: 201, statusText: 'Created' });
  });
});
